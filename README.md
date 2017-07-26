# Laravel Cardcom

## Installation

> **Note:** Laravel Cardcom is currently in beta.

Laravel Cardcom requires Laravel 5.4 or higher, and PHP 7.1+. You may use Composer to install Laravel Cardcom into your Laravel project:

    composer require yadahan/laravel-cardcom

You must install this service provider.

```php
// config/app.php
'providers' => [
    ...
    Yadahan\Cardcom\CardcomServiceProvider::class,
    ...
];
```

This package also comes with a facade, which provides an easy way to call the the class.

```php
// config/app.php
'aliases' => [
    ...
    'Cardcom' => Yadahan\Cardcom\Facades\Cardcom::class,
    ...
];
```

## Contributing

Thank you for considering contributing to the Laravel Cardcom.

## License

Laravel Cardcom is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).