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
    protected $url = 'https://secure.cardcom.co.il/';

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
        $this->config($config);
    }

    /**
     * Set the Cardcom terminal.
     *
     * @param array $config
     *
     * @return $this
     */
    public function config(array $config)
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
        $this->card['year'] = strlen($year) == 2 ? "20{$year}" : $year;
        $this->card['cvv'] = $cvv;
        $this->card['identity'] = $identity;

        return $this;
    }

    /**
     * Set the token.
     *
     * @param string $number
     * @param string $month
     * @param string $year
     * @param string $cvv
     * @param string $identity
     *
     * @return $this
     */
    public function token($number, $month, $year, $cvv = null, $identity = null)
    {
        $this->card['type'] = 'token';
        $this->card['number'] = $number;
        $this->card['month'] = $month;
        $this->card['year'] = strlen($year) == 2 ? "20{$year}" : $year;
        $this->card['cvv'] = $cvv;
        $this->card['identity'] = $identity;

        return $this;
    }

    /**
     * Set the invoice.
     *
     * @param array $options
     *
     * @return $this
     */
    public function invoice(array $options = [])
    {
        $this->invoice = $options;

        return $this;
    }

    /**
     * Set the item.
     *
     * @param array $options
     *
     * @return $this
     */
    public function item(array $options = [])
    {
        $this->invoice['items'][] = $options;

        return $this;
    }

    /**
     * Charge given amount.
     *
     * @param int    $amount
     * @param string $currency
     * @param string $payments
     *
     * @return mixed
     */
    public function charge($amount, $currency = 'ILS', $payments = 1, $approval = null)
    {
        $items = [];
        $invoice = [];
        $client = new GuzzleHttp\Client();

        if (is_array($this->card)) {
            if ($this->card['type'] === 'card') {
                $charge = [
                    'terminalNumber'    => $this->terminal,
                    'username'          => $this->username,
                    'sum'               => $amount,
                    'coinId'            => $this->currency($currency),
                    'numOfPayments'     => $payments,
                    'cardNumber'        => $this->card['number'],
                    'cardValIdityMonth' => $this->card['month'],
                    'cardValIdityYear'  => $this->card['year'],
                    'cvv'               => $this->card['cvv'],
                    'identityNumber'    => $this->card['identity'],
                ];

                $url = 'BillGoldPost2.aspx';
                $separator = ';';
            } elseif ($this->card['type'] === 'token') {
                $charge = [
                    'terminalNumber'                  => $this->terminal,
                    'username'                        => $this->username,
                    'TokenToCharge.SumToBill'         => $amount,
                    'TokenToCharge.CoinID'            => $this->currency($currency),
                    'TokenToCharge.NumOfPayments'     => $payments,
                    'TokenToCharge.Token'             => $this->card['number'],
                    'TokenToCharge.CardValidityMonth' => $this->card['month'],
                    'TokenToCharge.CardValidityYear'  => $this->card['year'],
                    'TokenToCharge.ApprovalNumber'    => $approval,
                    'TokenToCharge.IdentityNumber'    => $this->card['identity'],
                ];

                if (!empty($this->invoice)) {
                    $invoice = [
                        'InvoiceHead.CustName'                  => $this->invoice['customer_name'],
                        'InvoiceHead.SendByEmail'               => $this->invoice['send_email'] ?? 'true',
                        'InvoiceHead.Language'                  => $this->invoice['invoice_language'] ?? 'he',
                        'InvoiceHead.Email'                     => $this->invoice['email'] ?? null,
                        'InvoiceHead.CustAddresLine1'           => $this->invoice['address_1'] ?? null,
                        'InvoiceHead.CustAddresLine2'           => $this->invoice['address_2'] ?? null,
                        'InvoiceHead.CustCity'                  => $this->invoice['city'] ?? null,
                        'InvoiceHead.CustLinePH'                => $this->invoice['phone'] ?? null,
                        'InvoiceHead.CustMobilePH'              => $this->invoice['mobile'] ?? null,
                        'InvoiceHead.CompID'                    => $this->invoice['customer_id'] ?? null,
                        'InvoiceHead.Comments'                  => $this->invoice['comments'] ?? null,
                        'InvoiceHead.CoinID'                    => $this->currency($this->invoice['currency'] ?? $currency),
                        'InvoiceHead.ExtIsVatFree'              => $this->invoice['vat_free'] ?? 'false',
                        'InvoiceHead.ManualInvoiceNumber'       => $this->invoice['invoice_number'] ?? null,
                        'InvoiceHead.IsAutoCreateUpdateAccount' => $this->invoice['account'] ?? 'true',
                        'InvoiceHead.AccountForeignKey'         => $this->invoice['key'] ?? null,
                        'InvoiceHead.Date'                      => $this->invoice['invoice_date'] ?? null,
                        'InvoiceHead.DepartmentId'              => $this->invoice['department_id'] ?? null,
                        'InvoiceHead.SiteUniqueId'              => $this->invoice['unique_id'] ?? null,
                    ];

                    foreach ($this->invoice['items'] as $key => $item) {
                        $line = $key+1;

                        $items["InvoiceLines{$line}.Description"] = $item['description'];
                        $items["InvoiceLines{$line}.Price"] = $item['price'];
                        $items["InvoiceLines{$line}.Quantity"] = $item['quantity'] ?? '1';
                        $items["InvoiceLines{$line}.ProductID"] = $item['id'] ?? null;
                        $items["InvoiceLines{$line}.IsVatFree"] = $item['vat_free'] ?? null;
                    }
                }

                $url = 'interface/ChargeToken.aspx';
                $separator = '&';
            }
        }

        $params = array_merge($charge, $invoice, $items);

        $response = $client->request('POST', $this->url.$url, [
            'form_params' => $params,
        ]);

        return $this->rsponse('charge-'.$this->card['type'], $response->getBody(), $separator);
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
        $client = new GuzzleHttp\Client();

        if (is_array($this->card)) {
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

                $url = 'BillGoldPost2.aspx';
                $separator = ';';
            } elseif ($this->card['type'] === 'token') {
                $params = [
                    'terminalNumber'                      => $this->terminal,
                    'username'                            => $this->apiName,
                    'TokenToCharge.UserPassword'          => $this->apiPassword,
                    'TokenToCharge.RefundInsteadOfCharge' => 'True',
                    'TokenToCharge.SumToBill'             => $amount,
                    'TokenToCharge.CoinID'                => $this->currency($currency),
                    'TokenToCharge.NumOfPayments'         => $payments,
                    'TokenToCharge.Token'                 => $this->card['number'],
                    'TokenToCharge.CardValidityMonth'     => $this->card['month'],
                    'TokenToCharge.CardValidityYear'      => $this->card['year'],
                    'TokenToCharge.IdentityNumber'        => $this->card['identity'],
                ];

                $url = 'interface/ChargeToken.aspx';
                $separator = '&';
            }
        }

        $response = $client->request('POST', $this->url.$url, [
            'form_params' => $params,
        ]);

        return $this->rsponse('refund-'.$this->card['type'], $response->getBody(), $separator);
    }

    /**
     * Create token.
     *
     * @param array $options
     *
     * @return mixed
     */
    public function createToken(array $options = [])
    {
        $client = new GuzzleHttp\Client();

        if (is_array($this->card)) {
            $params = [
                'terminalNumber'    => $this->terminal,
                'username'          => $this->username,
                'cardNumber'        => $this->card['number'],
                'cardValIdityMonth' => $this->card['month'],
                'cardValIdityYear'  => $this->card['year'],
                'cvv'               => $this->card['cvv'],
                'identityNumber'    => $this->card['identity'],
                'tokenExpireDate'   => $options['expires'] ?? $this->card['month'].$this->card['year'],
            ];
        }

        $response = $client->request('POST', $this->url.'Tokens.aspx', [
            'form_params' => $params,
        ]);

        return $this->rsponse('create-token', $response->getBody());
    }

    /**
     * Suspend given amount.
     *
     * @param int    $amount
     * @param string $currency
     * @param string $payments
     * @param string $check
     *
     * @return mixed
     */
    public function suspend($amount, $currency = 'ILS', $payments = 1, $check = 'J5')
    {
        $client = new GuzzleHttp\Client();

        if (is_array($this->card)) {
            $params = [
                'terminalNumber'    => $this->terminal,
                'username'          => $this->username,
                'sum'               => $amount,
                'coinId'            => $this->currency($currency),
                'numOfPayments'     => $payments,
                'cardNumber'        => $this->card['number'],
                'cardValIdityMonth' => $this->card['month'],
                'cardValIdityYear'  => $this->card['year'],
                'cvv'               => $this->card['cvv'],
                'identityNumber'    => $this->card['identity'],
                'checkType'         => $check,
            ];
        }

        $response = $client->request('POST', $this->url.'SuspendedDealDeposit.aspx', [
            'form_params' => $params,
        ]);

        return $this->rsponse('suspend', $response->getBody());
    }

    /**
     * Response.
     *
     * @param string $action
     * @param string $response
     *
     * @return mixed
     */
    public function rsponse($action, $response, $separator = ';')
    {
        $array = explode($separator, $response);

        if (!is_array($array)) {
            return $response;
        }

        switch ($action) {
            case 'charge-card':
                $data = [
                    'code'        => $array[0],
                    'message'     => $array[2],
                    'transaction' => $array[1],
                ];
                break;

            case 'charge-token':
                $data = [
                    'code'        => explode('=', $array[0])[1],
                    'message'     => explode('=', $array[1])[1],
                    'transaction' => explode('=', $array[2])[1],
                    'approval'    => explode('=', $array[7])[1],
                    'invoice'     => [
                        'code'        => explode('=', $array[3])[1],
                        'message'     => explode('=', $array[4])[1],
                        'number'      => explode('=', $array[5])[1],
                        'type'        => explode('=', $array[6])[1],
                    ],
                ];
                break;

            case 'refund-card':
                $data = [
                    'code'        => $array[0],
                    'message'     => $array[2],
                    'transaction' => $array[1],
                ];
                break;

            case 'refund-token':
                $data = [
                    'code'        => explode('=', $array[0])[1],
                    'message'     => explode('=', $array[1])[1],
                    'transaction' => explode('=', $array[2])[1],
                    'approval'    => explode('=', $array[7])[1],
                    'invoice'     => [
                        'code'        => explode('=', $array[3])[1],
                        'message'     => explode('=', $array[4])[1],
                        'number'      => explode('=', $array[5])[1],
                        'type'        => explode('=', $array[6])[1],
                    ],
                ];
                break;

            case 'create-token':
                $data = [
                    'code'    => $array[0],
                    'message' => $array[1],
                    'token'   => $array[2],
                ];
                break;

            case 'suspend':
                $data = [
                    'code'        => $array[0],
                    'message'     => $array[2],
                    'transaction' => $array[1],
                ];
                break;

            default:
                throw new InvalidArgumentException("Unsupported action [{$action}].");
                break;
        }

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
        switch (strtoupper($code)) {
            case 'ILS':
                return 1;
                break;

            case 'USD':
                return 2;
                break;

            case 'AUD':
                return 36;
                break;

            case 'CAD':
                return 124;
                break;

            case 'DKK':
                return 208;
                break;

            case 'JPY':
                return 392;
                break;

            case 'NZD':
                return 554;
                break;

            case 'RUB':
                return 643;
                break;

            case 'CHF':
                return 756;
                break;

            case 'GBP':
                return 826;
                break;

            case 'EUR':
                return 978;
                break;

            default:
                throw new InvalidArgumentException("Unsupported currency [{$code}].");
                break;
        }
    }
}
