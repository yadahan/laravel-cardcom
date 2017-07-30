# Laravel Cardcom

[![Build Status](https://travis-ci.org/yadahan/laravel-cardcom.svg?branch=master)](https://travis-ci.org/yadahan/laravel-cardcom)
[![StyleCI](https://styleci.io/repos/98416118/shield?branch=master&style=flat)](https://styleci.io/repos/98416118)
[![Total Downloads](https://poser.pugx.org/yadahan/laravel-cardcom/downloads?format=flat)](https://packagist.org/packages/yadahan/laravel-cardcom)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg?style=flat)](https://raw.githubusercontent.com/yadahan/laravel-cardcom/master/LICENSE)

## Installation

> **Note:** Laravel Cardcom is currently in beta.

Laravel Cardcom requires Laravel 5.4 or higher, and PHP 7.0+. You may use Composer to install Laravel Cardcom into your Laravel project:

    composer require yadahan/laravel-cardcom

### Configuration

After installing the Laravel Cardcom, register the `Yadahan\Cardcom\CardcomServiceProvider` in your `config/app.php` configuration file:

```php
'providers' => [
    // Other service providers...

    Yadahan\Cardcom\CardcomServiceProvider::class,
],
```

Also, add the `Cardcom` facade to the `aliases` array in your `app` configuration file:

```php
'Cardcom' => Yadahan\Cardcom\Facades\Cardcom::class,
```

Next, publish its config using the `vendor:publish` Artisan command:

    php artisan vendor:publish --tag="cardcom-config"

You will also need to add credentials for your terminal. These credentials should be placed in your `config/cardcom.php` configuration file, For example:

```php
'terminals' => [
    'default' => [
        'terminal' => 'your-terminal',
        'username' => 'your-username',
        'api_name' => 'your-api-name',
        'api_password' => 'your-api-password',
    ]
]
```

### Basic Usage

Charge a credit card:

```php
Cardcom::card('4580000000000000', '01', '2020')->charge(10, 'ILS');
// With optional payments parameter
Cardcom::card('4580000000000000', '01', '2020')->charge(10, 'ILS', 3);
```

Create a credit card token:

```php
Cardcom::card('4580000000000000', '01', '2020')->createToken();
// With optional expiration date parameter
Cardcom::card('4580000000000000', '01', '2020')->createToken(['expires' => 'MMYYYY']);
```

Of course you can config the terminal you want to use:

```php
Cardcom::config(config('cardcom.terminals.other'))->card('4580000000000000', '01', '2020')->charge(10, 'ILS');
// Or
Cardcom::config(['terminal' => '1000', 'username' => 'barak9611'])->card('4580000000000000', '01', '2020')->charge(10, 'ILS');
```

## Contributing

Thank you for considering contributing to the Laravel Cardcom.

## License

Laravel Cardcom is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).