---
title: Normalizers
weight: 4
---

This package allows you to dynamically create data objects from any kind of object. For example, you can use an
eloquent model to create a data object like this:

```php
SongData::from(Song::findOrFail($id));
```

A `Normalizer` will take a payload like a model and will transform it into an array, so it can be used in the pipeline (see further).

By default, there are five normalizers:

- **ModelNormalizer** will cast eloquent models
- **ArrayableNormalizer** will cast `Arrayable`'s
- **ObjectNormalizer** will cast `stdObject`'s
- **ArrayNormalizer** will cast arrays
- **JsonNormalizer** will cast json strings

A sixth normalizer can be optionally enabled:

- **FormRequestNormalizer** will normalize a form request by calling the `validated` method

Normalizers can be globally configured in `config/data.php`, and can be configured on a specific data object by overriding the `normalizers` method.

```php
class SongData extends Data
{
    public function __construct(
        // ...
    ) {
    }

    public static function normalizers(): array
    {
        return [
            ModelNormalizer::class,
            ArrayableNormalizer::class,
            ObjectNormalizer::class,
            ArrayNormalizer::class,
            JsonNormalizer::class,
        ];
    }
}
```

A normalizer implements the `Normalizer` interface and should return an array representation of the payload, or null if it cannot normalize the payload:

```php
class ArrayableNormalizer implements Normalizer
{
    public function normalize(mixed $value): ?array
    {
        if (! $value instanceof Arrayable) {
            return null;
        }

        return $value->toArray();
    }
}
```

Normalizers are executed in the same order as they are defined in the `normalize` method. The first normalizer not returning null will be used to normalize the payload. Magical creation methods always have precedence over normalizers.
