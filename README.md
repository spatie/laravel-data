<div align="left">
    <a href="https://spatie.be/open-source?utm_source=github&utm_medium=banner&utm_campaign=laravel-data">
      <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://spatie.be/packages/header/laravel-data/html/dark.webp">
        <img alt="Logo for laravel-data" src="https://spatie.be/packages/header/laravel-data/html/light.webp">
      </picture>
    </a>

<h1>Powerful data objects for Laravel</h1>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-data.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-data)
[![Tests](https://github.com/spatie/laravel-data/actions/workflows/run-tests.yml/badge.svg)](https://github.com/spatie/laravel-data/actions/workflows/run-tests.yml)
[![PHPStan](https://github.com/spatie/laravel-data/actions/workflows/phpstan.yml/badge.svg)](https://github.com/spatie/laravel-data/actions/workflows/phpstan.yml)
[![Check & fix styling](https://github.com/spatie/laravel-data/actions/workflows/php-cs-fixer.yml/badge.svg)](https://github.com/spatie/laravel-data/actions/workflows/php-cs-fixer.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-data.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-data)
    
</div>

This package enables the creation of rich data objects which can be used in various ways. Using this package you only need to describe your data once:

- instead of a form request, you can use a data object
- instead of an API transformer, you can use a data object
- instead of manually writing a typescript definition, you can use... 🥁 a data object

A `laravel-data` specific object is just a regular PHP object that extends from `Data`:

```php
use Spatie\LaravelData\Data;

class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
    ) {
    }
}
```

By extending from `Data` you enable a lot of new functionality like:

- Automatically transforming data objects into resources (like the Laravel API resources)
- Transform only the requested parts of data objects with lazy properties
- Automatically creating data objects from request data and validating them
- Automatically resolve validation rules for properties within a data object
- Make it possible to construct a data object from any type you want
- Add support for automatically validating data objects when creating them
- Generate TypeScript definitions from your data objects you can use on the frontend
- Save data objects as properties of an Eloquent model
- And a lot more ...

Why would you be using this package?

- You can be sure that data is typed when it leaves your app and comes back again from the frontend which makes a lot less errors
- You don't have to write the same properties three times (in a resource, in a data transfer object and in request validation)
- You need to write a lot less of validation rules because they are obvious through PHP's type system
- You get TypeScript versions of the data objects for free

## Are you a visual learner?

In this talk, given at Laracon, you'll see [an introduction to Laravel Data](https://www.youtube.com/watch?v=CrO_7Df1cBc).

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-data.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-data)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Documentation

You will find full documentation on the dedicated [documentation](https://spatie.be/docs/laravel-data/v4/introduction) site.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ruben Van Assche](https://github.com/rubenvanassche)
- [Aidan Casey](https://github.com/aidan-casey) (Validation Attributes)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

