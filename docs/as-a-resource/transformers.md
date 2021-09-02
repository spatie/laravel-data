---
title: Transforming data
weight: 3
---

Each property of a data object should be transformed into a usable type to communicate via JSON. 

For the default types (string, bool, int, float and array) there are no complex transformations required, but special types like `Carbon` or a Laravel Model will need to extra attention.

Transformers are simple classes that will convert a complex type to something simple like a `string` or `int`. For example, a `Carbon` object can be transformed to `16-05-1994`, `16-05-1994T00:00:00+00` or something completely different.

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

Next to a `DateTimeInterfaceTransformer` the package also ships with an `ArraybleTransformer` that transforms an `Arrayable` to an array.

It is possible to create transformers for your own specific types [here](TODO).

## Global transformers

Global transformers are defined in the `data.php` config file and will be used when no local transformer for a property was added. By default, there are two transformers:

```php
/*
 * Global transformers will take complex types and transform them into simple
 * types.
 */
'transformers' => [
    DateTimeInterface::class => \Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer::class,
    \Illuminate\Contracts\Support\Arrayable::class => \Spatie\LaravelData\Transformers\ArrayableTransformer::class,
],
```

The package will look through these global transformers and tries to find a suitable transformer. You can define transformers for:

- a **specific implementation** (e.g. CarbonImmutable)
- an **interface** (e.g. DateTimeInterface)
- a **base class** (e.g. Enum)


