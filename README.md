# Laravel Cardcom

## Installation

This package can be installed through Composer.

``` bash
composer require yadahan/cardcom
```

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

Thank you for considering contributing to the Cardcom.

## License

Laravel Cardcom is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).