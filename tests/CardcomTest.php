<?php

use Orchestra\Testbench\TestCase;
use Yadahan\Cardcom\Cardcom;

class CardcomTest extends TestCase
{
    protected $terminal = '1000';
    protected $username = 'barak9611';
    protected $apiName = 'kzFKfohEvL6AOF8aMEJz';
    protected $apiPassword = 'FIDHIh4pAadw3Slbdsjg';

    public function test_charge_card()
    {
        $cardcom = new Cardcom([
            'terminal' => $this->terminal,
            'username' => $this->username,
        ]);

        $response = $cardcom->card('4580000000000000', '01', '2020')->charge(15, 'ILS');

        $this->assertEquals('0', $response['code']);
    }

    public function test_charge_card_in_payments()
    {
        $cardcom = new Cardcom([
            'terminal' => $this->terminal,
            'username' => $this->username,
        ]);

        $response = $cardcom->card('4580000000000000', '01', '2020')->charge(10, 'ILS', 3);

        $this->assertEquals('0', $response['code']);
    }

    public function test_refund_card()
    {
        $cardcom = new Cardcom([
            'terminal'     => $this->terminal,
            'username'     => $this->username,
            'api_name'     => $this->apiName,
            'api_password' => $this->apiPassword,
        ]);

        $response = $cardcom->card('4580000000000000', '01', '2020')->refund(10, 'ILS');

        $this->assertEquals('0', $response['code']);
    }

    public function test_refund_card_in_payments()
    {
        $cardcom = new Cardcom([
            'terminal'     => $this->terminal,
            'username'     => $this->username,
            'api_name'     => $this->apiName,
            'api_password' => $this->apiPassword,
        ]);

        $response = $cardcom->card('4580000000000000', '01', '2020')->refund(10, 'ILS', 3);

        $this->assertEquals('0', $response['code']);
    }

    public function test_cancel_transaction()
    {
        $cardcom = new Cardcom([
            'terminal'     => $this->terminal,
            'username'     => $this->username,
            'api_name'     => $this->apiName,
            'api_password' => $this->apiPassword,
        ]);

        $transaction = $cardcom->card('4580000000000000', '01', '2020')->charge(15, 'ILS');

        $this->assertEquals('0', $transaction['code']);

        $response = $cardcom->cancel($transaction['transaction'], true);

        $this->assertEquals('0', $response['code']);
    }

    public function test_refund_transaction()
    {
        $cardcom = new Cardcom([
            'terminal'     => $this->terminal,
            'username'     => $this->username,
            'api_name'     => $this->apiName,
            'api_password' => $this->apiPassword,
        ]);

        $transaction = $cardcom->card('4580000000000000', '01', '2020')->charge(15, 'ILS');

        $this->assertEquals('0', $transaction['code']);

        $response = $cardcom->cancel($transaction['transaction'], false, 10);

        $this->assertEquals('0', $response['code']);
    }

    public function test_create_card_token()
    {
        $cardcom = new Cardcom([
            'terminal'     => $this->terminal,
            'username'     => $this->username,
            'api_name'     => $this->apiName,
            'api_password' => $this->apiPassword,
        ]);

        $response = $cardcom->card('4580000000000000', '01', '2020')->createToken();

        $this->assertEquals('0', $response['code']);
    }

    public function test_charge_token()
    {
        $cardcom = new Cardcom([
            'terminal' => $this->terminal,
            'username' => $this->username,
        ]);

        $token = $cardcom->card('4580000000000000', '01', '2020')->createToken();

        $this->assertEquals('0', $token['code']);

        $response = $cardcom->token($token['token'], '01', '2020')->charge(10, 'ILS');

        $this->assertEquals('0', $response['code']);
    }

    public function test_charge_token_in_payments()
    {
        $cardcom = new Cardcom([
            'terminal' => $this->terminal,
            'username' => $this->username,
        ]);

        $token = $cardcom->card('4580000000000000', '01', '2020')->createToken();

        $this->assertEquals('0', $token['code']);

        $response = $cardcom->token($token['token'], '01', '2020')->charge(10, 'ILS', 3);

        $this->assertEquals('0', $response['code']);
    }

    public function test_refund_token()
    {
        $cardcom = new Cardcom([
            'terminal'     => $this->terminal,
            'username'     => $this->username,
            'api_name'     => $this->apiName,
            'api_password' => $this->apiPassword,
        ]);

        $token = $cardcom->card('4580000000000000', '01', '2020')->createToken();

        $this->assertEquals('0', $token['code']);

        $response = $cardcom->token($token['token'], '01', '2020')->refund(10, 'ILS');

        $this->assertEquals('0', $response['code']);
    }

    public function test_refund_token_in_payments()
    {
        $cardcom = new Cardcom([
            'terminal'     => $this->terminal,
            'username'     => $this->username,
            'api_name'     => $this->apiName,
            'api_password' => $this->apiPassword,
        ]);

        $token = $cardcom->card('4580000000000000', '01', '2020')->createToken();

        $this->assertEquals('0', $token['code']);

        $response = $cardcom->token($token['token'], '01', '2020')->refund(10, 'ILS', 3);

        $this->assertEquals('0', $response['code']);
    }

    public function test_charge_token_and_create_invoice()
    {
        $cardcom = new Cardcom([
            'terminal' => $this->terminal,
            'username' => $this->username,
        ]);

        $token = $cardcom->card('4580000000000000', '01', '2020')->createToken();

        $this->assertEquals('0', $token['code']);

        $response = $cardcom->token($token['token'], '01', '2020')
                    ->invoice([
                        'customer_name'    => 'Test Test',
                        'send_email'       => 'true',
                        'invoice_language' => 'he',
                        'email'            => 'test@test.com',
                        'address_1'        => 'Address line 1',
                        'address_2'        => 'Address line 2',
                        'city'             => 'Test city',
                        'phone'            => '031234567',
                        'mobile'           => '0501234567',
                        'customer_id'      => '1',
                        'comments'         => 'Test comments',
                        'currency'         => 'ILS',
                        'vat_free'         => 'false',
                        'account'          => 'true',
                        'key'              => '1',
                    ])
                    ->invoiceItem([
                        'description' => 'Test Product 1',
                        'price'       => '5',
                        'quantity'    => '1',
                        'id'          => '1',
                        'vat_free'    => 'true',
                    ])
                    ->invoiceItem([
                        'description' => 'Test Product 2',
                        'price'       => '5',
                        'quantity'    => '1',
                        'id'          => '2',
                    ])
                    ->charge(10, 'ILS');

        $this->assertEquals('0', $response['code']);
        $this->assertEquals('0', $response['invoice']['code']);
    }

    public function test_create_suspended_transaction()
    {
        $cardcom = new Cardcom([
            'terminal' => $this->terminal,
            'username' => $this->username,
        ]);

        $response = $cardcom->card('4580000000000000', '01', '2020')->suspend(10, 'ILS');

        $this->assertEquals('0', $response['code']);
    }

    public function test_charge_suspended_transaction()
    {
        $cardcom = new Cardcom([
            'terminal' => $this->terminal,
            'username' => $this->username,
        ]);

        $suspended = $cardcom->card('4580000000000000', '01', '2020')->suspend(10, 'ILS');

        $this->assertEquals('0', $suspended['code']);

        $response = $cardcom->token($suspended['token'], '01', '2020')->chargeSuspended($suspended['approval'], 10, 'ILS');

        $this->assertEquals('0', $response['code']);
    }

    public function test_config_terminal()
    {
        $cardcom = new Cardcom([
            'terminal' => $this->terminal,
            'username' => $this->username,
        ]);

        $response = $cardcom->setConfig(['terminal' => '100', 'username' => 'card9611'])->card('4580000000000000', '01', '2020')->charge(10, 'ILS');

        $this->assertEquals('501', $response['code']);
    }
}
