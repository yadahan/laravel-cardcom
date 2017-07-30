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
     * @var string
     */
    protected $card;

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
        $this->card['number'] = $number;
        $this->card['month'] = $month;
        $this->card['year'] = strlen($year) == 2 ? "20{$year}" : $year;
        $this->card['cvv'] = $cvv;
        $this->card['identity'] = $identity;

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
    public function charge($amount, $currency = 'ILS', $payments = 1)
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
            ];
        }

        $response = $client->request('POST', $this->url.'BillGoldPost2.aspx', [
            'form_params' => $params,
        ]);

        return $this->rsponse('charge', $response->getBody());
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
        }

        $response = $client->request('POST', $this->url.'BillGoldPost2.aspx', [
            'form_params' => $params,
        ]);

        return $this->rsponse('refund', $response->getBody());
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

        return $this->rsponse('token', $response->getBody());
    }

    /**
     * Response.
     *
     * @param string $action
     * @param string $response
     *
     * @return mixed
     */
    public function rsponse($action, $response)
    {
        $array = explode(';', $response);

        if (!is_array($array)) {
            return $response;
        }

        switch ($action) {
            case 'charge':
                $data = [
                    'code'        => $array[0],
                    'message'     => $array[2],
                    'transaction' => $array[1],
                ];
                break;

            case 'refund':
                $data = [
                    'code'        => $array[0],
                    'message'     => $array[2],
                    'transaction' => $array[1],
                ];
                break;

            case 'token':
                $data = [
                    'code'    => $array[0],
                    'message' => $array[1],
                    'token'   => $array[2],
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
