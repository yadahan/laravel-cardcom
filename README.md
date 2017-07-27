# Laravel Cardcom

## Installation

> **Note:** Laravel Cardcom is currently in beta.

Laravel Cardcom requires Laravel 5.4 or higher, and PHP 7.1+. You may use Composer to install Laravel Cardcom into your Laravel project:

    composer require yadahan/laravel-cardcom

After installing Laravel Cardcom, publish its config using the `vendor:publish` Artisan command:

    php artisan vendor:publish --tag="cardcom-config"

### Configuration

After installing the Laravel Cardcom library, register the `Yadahan\Cardcom\CardcomServiceProvider` in your `config/app.php` configuration file:

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

You will also need to add credentials for your terminal. These credentials should be placed in your `config/cardcom.php` configuration file, For example:
```php
'terminals' => [
    'default' => [
        'terminal' => 1000,
        'username' => 'card9611',
        'api_name' => 'your-api-name',
        'api_password' => 'your-api-password',
    ]
]
```

### Basic Usage

Next, you are ready to charge credit card:

```php
Cardcom::card('4580000000000000', '01', '2020')->charge(10, 'ILS');
```

Of course you can config the terminal you want to use:

```php
Cardcom::config(config('cardcom.terminals.other'))->card('4580000000000000', '01', '2020')->charge(10, 'ILS');
// Or
Cardcom::config(['terminal' => '1000', 'username' => 'card9611'])->card('4580000000000000', '01', '2020')->charge(10, 'ILS');
```

## Contributing

Thank you for considering contributing to the Laravel Cardcom.

## License

Laravel Cardcom is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).