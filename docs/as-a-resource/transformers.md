---
title: Transforming data
weight: 7
---

Transformers allow you to transform complex types to simple types. This is useful when you want to transform a data object to an array or JSON.

No complex transformations are required for the default types (string, bool, int, float, enum and array), but special types like `Carbon` or a Laravel Model will need extra attention.

Transformers are simple classes that will convert a such complex types to something simple like a `string` or `int`. For example, we can transform a `Carbon` object to `16-05-1994`, `16-05-1994T00:00:00+00` or something completely different.

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

It is possible to create transformers for your specific types. You can find more info [here](/docs/laravel-data/v4/advanced-usage/creating-a-transformer).

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

Internally the package uses the `transform` method for operations like `toArray`, `all`, `toJson` and so on. This method is highly configurable, when calling it without any arguments it will behave like the `toArray` method:

```php
ArtistData::from($artist)->transform();
```

Producing the following result:

```php
[
    'name' => 'Rick Astley',
    'birth_date' => '06-02-1966',
]
```

It is possible to disable the transformation of values, which will make the `transform` method behave like the `all` method:

```php
use Spatie\LaravelData\Support\Transformation\TransformationContext;

ArtistData::from($artist)->transform(
    TransformationContextFactory::create()->withoutValueTransformation()
);
```

Outputting the following array:

```php
[
    'name' => 'Rick Astley',
    'birth_date' => Carbon::parse('06-02-1966'),
]
```

The [mapping of property names](/docs/laravel-data/v4/as-a-resource/mapping-property-names) can also be disabled:

```php
ArtistData::from($artist)->transform(
    TransformationContextFactory::create()->withoutPropertyNameMapping()
);
```

It is possible to enable [wrapping](/docs/laravel-data/v4/as-a-resource/wrapping-data) the data object:

```php
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;

ArtistData::from($artist)->transform(
    TransformationContextFactory::create()->withWrapping()
);
```

Outputting the following array:

```php
[
    'data' => [
        'name' => 'Rick Astley',
        'birth_date' => '06-02-1966',
    ],
]
```

You can also add additional global transformers as such:

```php
ArtistData::from($artist)->transform(
    TransformationContextFactory::create()->withGlobalTransformer(
        'string', 
        StringToUpperTransformer::class
    )
);
```

## Transformation depth

When transforming a complicated structure of nested data objects it is possible that an infinite loop is created of data objects including each other.
To prevent this, a transformation depth can be set, when that depth is reached when transforming, either an exception will be thrown or an empty
array is returned, stopping the transformation.

This transformation depth can be set globally in the `data.php` config file:

```php
'max_transformation_depth' => 20,
```

Setting the transformation depth to `null` will disable the transformation depth check:

```php
'max_transformation_depth' => null,
```

It is also possible if a `MaxTransformationDepthReached` exception should be thrown or an empty array should be returned:

```php
'throw_when_max_transformation_depth_reached' => true,
```

It is also possible to set the transformation depth on a specific transformation by using a `TransformationContextFactory`:

```php
ArtistData::from($artist)->transform(
    TransformationContextFactory::create()->maxDepth(20)
);
```

By default, an exception will be thrown when the maximum transformation depth is reached. This can be changed to return an empty array as such:

```php
ArtistData::from($artist)->transform(
    TransformationContextFactory::create()->maxDepth(20, throw: false)
);
```
