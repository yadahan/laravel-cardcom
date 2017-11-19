<?php

namespace Yadahan\Cardcom;

use GuzzleHttp;
use InvalidArgumentException;

class Cardcom
{
    /**
     * The base Cardcom Api URL.
     *
     * @var string
     */
    protected $url = 'https://secure.cardcom.co.il';

    /**
     * The Cardcom terminal number.
     *
     * @var string
     */
    protected $terminal;

    /**
     * The Cardcom terminal username.
     *
     * @var string
     */
    protected $username;

    /**
     *  The Cardcom api name.
     *
     * @var string
     */
    protected $apiName;

    /**
     *  The Cardcom api password.
     *
     * @var string
     */
    protected $apiPassword;

    /**
     *  Credit card.
     *
     * @var array
     */
    protected $card;

    /**
     *  Invoice.
     *
     * @var array
     */
    protected $invoice;

    /**
     * Cardcom constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->setConfig($config);
    }

    /**
     * Set the Cardcom terminal.
     *
     * @param array $config
     *
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->terminal = $config['terminal'];
        $this->username = $config['username'];
        $this->apiName = $config['api_name'] ?? '';
        $this->apiPassword = $config['api_password'] ?? '';

        return $this;
    }

    /**
     * Set the credit card.
     *
     * @param string $number
     * @param string $month
     * @param string $year
     * @param string $cvv
     * @param string $identity
     *
     * @return $this
     */
    public function card($number, $month, $year, $cvv = null, $identity = null)
    {
        $this->card['type'] = 'card';
        $this->card['number'] = $number;
        $this->card['month'] = $month;
        $this->card['year'] = substr($year, -2);
        $this->card['cvv'] = $cvv;
        $this->card['identity'] = $identity;

        return $this;
    }

    /**
     * Set the token.
     *
     * @param string $token
     * @param string $month
     * @param string $year
     *
     * @return $this
     */
    public function token($token, $month, $year)
    {
        $this->card($token, $month, $year);
        $this->card['type'] = 'token';

        return $this;
    }

    /**
     * Set the invoice.
     *
     * @param array $params
     *
     * @return $this
     */
    public function invoice(array $params)
    {
        $this->invoice = $params;

        return $this;
    }

    /**
     * Add invoice item.
     *
     * @param array $params
     *
     * @return $this
     */
    public function invoiceItem(array $params)
    {
        $this->invoice['items'][] = $params;

        return $this;
    }

    /**
     * Charge given amount.
     *
     * @param int    $amount
     * @param string $currency
     * @param string $payments
     * @param bool   $createToken
     * @param string $approval
     *
     * @return mixed
     */
    public function charge($amount, $currency = 'ILS', $payments = 1, $createToken = false, $approval = null)
    {
        $items = [];
        $invoice = [];
        $charge = [
            'terminalNumber'    => $this->terminal,
            'userName'          => $this->username,
            'sum'               => $amount,
            'coinId'            => $this->currency($currency),
            'numOfPayments'     => $payments,
            'cardValIdityMonth' => $this->card['month'],
            'cardValIdityYear'  => $this->card['year'],
            'identityNumber'    => $this->card['identity'],
        ];

        if ($this->card['type'] == 'token') {
            $charge['token'] = $this->card['number'];
        }

        if ($this->card['type'] == 'card') {
            $charge['cardNumber'] = $this->card['number'];
            $charge['cvv'] = $this->card['cvv'];
            $charge['createToken'] = $createToken;
        }

        if ($approval) {
            $charge['approvalNumber'] = $approval;
        }

        if (! empty($this->invoice)) {
            $invoice = [
                'invCreateInvoice'   => true,
                'invCusAddress1'     => $this->invoice['address_1'] ?? null,
                'invCusAddress2'     => $this->invoice['address_2'] ?? null,
                'invCusCity'         => $this->invoice['city'] ?? null,
                'invDestEmail'       => $this->invoice['email'],
                'invCustName'        => $this->invoice['customer_name'],
                'InvCustLinePH'      => $this->invoice['phone'] ?? null,
                'InvCustMobilePH'    => $this->invoice['mobile'] ?? null,
                'InvComments'        => $this->invoice['comments'] ?? null,
                'invLanguages'       => $this->invoice['invoice_language'] ?? 'he',
                'InvNoVat'           => $this->invoice['no_vat'] ?? false,
            ];

            if (! isset($this->invoice['items']) || empty($this->invoice['items'])) {
                $invoice['invItemDescription'] = $this->invoice['description'] ?? '';
                $invoice['InvProductID'] = $this->invoice['product_id'] ?? null;
            } else {
                foreach ($this->invoice['items'] as $key => $item) {
                    $line = $key == 0 ? '' : $key;

                    $items["InvExtLine{$line}.Description"] = $item['description'];
                    $items["InvExtLine{$line}.PriceIncludeVAT"] = $item['price'];
                    $items["InvExtLine{$line}.Quantity"] = $item['quantity'] ?? '1';
                    $items["InvExtLine{$line}.ProductID"] = $item['id'] ?? null;
                    $items["InvExtLine{$line}.IsVatFree"] = $item['vat_free'] ?? false;
                }
            }
        }

        return $this->request(array_merge($charge, $invoice, $items));
    }

    /**
     * Refund a credit card charge.
     *
     * @param int    $amount
     * @param string $currency
     * @param string $payments
     *
     * @return mixed
     */
    public function refund($amount, $currency = 'ILS', $payments = 1)
    {
        if ($this->card['type'] === 'card') {
            $params = [
                'terminalNumber'    => $this->terminal,
                'username'          => $this->apiName,
                'userPassword'      => $this->apiPassword,
                'dealType'          => '51',
                'sum'               => $amount,
                'coinId'            => $this->currency($currency),
                'numOfPayments'     => $payments,
                'cardNumber'        => $this->card['number'],
                'cardValIdityMonth' => $this->card['month'],
                'cardValIdityYear'  => $this->card['year'],
                'cvv'               => $this->card['cvv'],
                'identityNumber'    => $this->card['identity'],
            ];

            return $this->request($params, '/BillGoldPost2.aspx', ';');
        }

        if ($this->card['type'] === 'token') {
            $params = [
                'terminalNumber'                      => $this->terminal,
                'username'                            => $this->apiName,
                'tokenToCharge.userPassword'          => $this->apiPassword,
                'tokenToCharge.refundInsteadOfCharge' => 'True',
                'tokenToCharge.sumToBill'             => $amount,
                'tokenToCharge.coinID'                => $this->currency($currency),
                'tokenToCharge.numOfPayments'         => $payments,
                'tokenToCharge.token'                 => $this->card['number'],
                'tokenToCharge.cardValidityMonth'     => $this->card['month'],
                'tokenToCharge.cardValidityYear'      => $this->card['year'],
                'tokenToCharge.identityNumber'        => $this->card['identity'],
            ];

            return $this->request($params, '/Interface/ChargeToken.aspx');
        }
    }

    /**
     * Cancel a transaction.
     *
     * @param int  $transaction
     * @param bool $cancel
     * @param bool $partial
     *
     * @return mixed
     */
    public function cancel($transaction, $cancel = false, $partial = 0)
    {
        $params = [
            'terminalNumber'      => $this->terminal,
            'name'                => $this->apiName,
            'pass'                => $this->apiPassword,
            'internalDealNumber'  => $transaction,
            'cancelOnly'          => $cancel,
        ];

        if ($partial) {
            $params['partialSum'] = $partial;
        }

        return $this->request($params, '/Interface/CancelDeal.aspx');
    }

    /**
     * Create token.
     *
     * @param array $options
     *
     * @return mixed
     */
    public function createToken()
    {
        $params = [
            'terminalNumber'    => $this->terminal,
            'username'          => $this->username,
            'cardNumber'        => $this->card['number'],
            'cardValidityMonth' => $this->card['month'],
            'cardValidityYear'  => $this->card['year'],
            'cvv'               => $this->card['cvv'],
            'identityNumber'    => $this->card['identity'],
            'createToken'       => true,
        ];

        return $this->request($params);
    }

    /**
     * Suspend given amount.
     *
     * @param int    $amount
     * @param string $currency
     * @param int    $payments
     * @param int    $check
     * @param bool   $createToken
     *
     * @return mixed
     */
    public function suspend($amount, $currency = 'ILS', $payments = 1, $check = 5, $createToken = true)
    {
        $params = [
            'terminalNumber'    => $this->terminal,
            'username'          => $this->username,
            'jParameter'        => $check,
            'sum'               => $amount,
            'coinId'            => $this->currency($currency),
            'cardNumber'        => $this->card['number'],
            'cardValidityMonth' => $this->card['month'],
            'cardValidityYear'  => $this->card['year'],
            'cvv'               => $this->card['cvv'],
            'identityNumber'    => $this->card['identity'],
            'createToken'       => $createToken,
        ];

        return $this->request($params);
    }

    /**
     * Charge suspended transaction.
     *
     * @param int $transaction
     *
     * @return mixed
     */
    public function chargeSuspended($approval, $amount, $currency = 'ILS', $payments = 1)
    {
        return $this->charge($amount, $currency, $payments, $createToken = false, $approval);
    }

    /**
     * Request.
     *
     * @param array  $params
     * @param string $path
     * @param string $separator
     *
     * @return mixed
     */
    protected function request($params, $path = '/Interface/Direct.aspx', $separator = '&')
    {
        $client = new GuzzleHttp\Client();

        $response = $client->request('POST', $this->url.$path, [
            'form_params' => $params,
        ]);

        return $this->response($response->getBody(), $separator);
    }

    /**
     * Response.
     *
     * @param string $action
     * @param string $response
     *
     * @return mixed
     */
    public function response($response, $separator = '&')
    {
        if ($separator == '&') {
            parse_str($response, $array);

            $data = [
                'code'        => $array['ResponseCode'],
                'message'     => $array['Description'],
                'transaction' => $array['InternalDealNumber'],
            ];
        }

        if ($separator == ';') {
            $array = explode($separator, $response);

            $data = [
                'code'        => $array[0],
                'message'     => $array[2],
                'transaction' => $array[1],
            ];
        }

        if (! is_array($array)) {
            return $response;
        }

        if (isset($array['Token'])) {
            $data['token'] = $array['Token'];
        }

        if (isset($array['ApprovalNumber'])) {
            $data['approval'] = $array['ApprovalNumber'];
        }

        if (isset($array['InvoiceResponse_ResponseCode'])) {
            $data['invoice'] = [
                'code'    => $array['InvoiceResponse_ResponseCode'],
                'message' => $array['InvoiceResponse_Description'],
                'number'  => $array['InvoiceResponse_InvoiceNumber'],
                'type'    => $array['InvoiceResponse_InvoiceType'],
            ];
        }

        $data['payload'] = $array;

        return $data;
    }

    /**
     * Cardcom currency supported.
     * More information at http://kb.cardcom.co.il/article/AA-00247/0.
     *
     * @param string $code
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    public function currency($code)
    {
        $currency = strtoupper($code);

        $currencies = [
            'ILS' => 1,
            'USD' => 2,
            'AUD' => 36,
            'CAD' => 124,
            'DKK' => 208,
            'JPY' => 392,
            'NZD' => 554,
            'RUB' => 643,
            'CHF' => 756,
            'GBP' => 826,
            'EUR' => 978
        ];

        if (! isset($currencies[$currency])) {
            throw new InvalidArgumentException("Unsupported currency [{$code}].");
        }

        return $currencies[$currency];
    }
}
