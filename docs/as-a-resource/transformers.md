---
title: Transforming data
weight: 4
---

Each property of a data object should be transformed into a usable type to communicate via JSON.

No complex transformations are required for the default types (string, bool, int, float, enum and array), but special types like `Carbon` or a Laravel Model will need extra attention.

Transformers are simple classes that will convert a complex type to something simple like a `string` or `int`. For example, we can transform a `Carbon` object to `16-05-1994`, `16-05-1994T00:00:00+00` or something completely different.

There are two ways you can define transformers: locally and globally.

## Local transformers

When you want to transform a specific property, you can use an attribute with the transformer you want to use:

```php
class ArtistData extends Data{
    public function __construct(
        public string $name,
        #[WithTransformer(DateTimeInterfaceTransformer::class)]
        public Carbon $birth_date
    ) {
    }
}
```

The `DateTimeInterfaceTransformer` is shipped with the package and will transform objects of type `Carbon`, `CarbonImmutable`, `DateTime` and `DateTimeImmutable` to a string.

The format used for converting the date to string can be set in the `data.php` config file. It is also possible to manually define a format:

```php
class ArtistData extends Data{
    public function __construct(
        public string $name,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: 'm-Y')]
        public Carbon $birth_date
    ) {
    }
}
```

Next to a `DateTimeInterfaceTransformer` the package also ships with an `ArrayableTransformer` that transforms an `Arrayable` object to an array.

It is possible to create transformers for your specific types. You can find more info [here](/docs/laravel-data/v3/advanced-usage/creating-a-transformer).

## Global transformers

Global transformers are defined in the `data.php` config file and are used when no local transformer for a property was added. By default, there are two transformers:

```php
use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelData\Transformers\ArrayableTransformer;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

/*
 * Global transformers will take complex types and transform them into simple
 * types.
 */
'transformers' => [
    DateTimeInterface::class => DateTimeInterfaceTransformer::class,
    Arrayable::class => ArrayableTransformer::class,
],
```

The package will look through these global transformers and tries to find a suitable transformer. You can define transformers for:

- a **specific implementation** (e.g. CarbonImmutable)
- an **interface** (e.g. DateTimeInterface)
- a **base class** (e.g. Enum)

## Getting a data object without transforming

It is possible to get an array representation of a data object without transforming the properties. This means `Carbon` objects won't be transformed into strings. And also, nested data objects and `DataCollection`s won't be transformed into arrays. You can do this by calling the `all` method on a data object like this:

```php
ArtistData::from($artist)->all();
```

## Getting a data object (on steroids)

Internally the package uses the `transform` method for operations like `toArray`, `all`, `toJson` and so on. This method is highly configurable:

```php
ArtistData::from($artist)->transform(
    bool $transformValues = true,
    WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
    bool $mapPropertyNames = true,
);
```

- **$transformValues** when enabled transformers will be used to transform properties, also data objects and collections will be transformed
- **$wrapExecutionType** allows you to set if wrapping is `Enabled` or `Disabled`
- **$mapPropertyNames** uses defined mappers to rename properties when enabled
