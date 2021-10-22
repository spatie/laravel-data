# Typed resources for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-data.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-data-resource)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-data/run-tests?label=tests)](https://github.com/spatie/laravel-data-resource/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-data/Check%20&%20fix%20styling?label=code%20style)](https://github.com/spatie/laravel-data-resource/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-data.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-data-resource)

This package tries to be a layer between your backend and the frontend code. By creating a structured data model that both sides must strictly implement we can ensure data will be communicated correctly.

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

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-data.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-data)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Documentation

You will find full documentation on the dedicated [documentation](https://spatie.be/docs/laravel-data/v1/introduction) site.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ruben Van Assche](https://github.com/rubenvanassche)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

