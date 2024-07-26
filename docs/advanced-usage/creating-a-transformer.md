---
title: Creating a transformer
weight: 7
---

Transformers take complex values and transform them into simple types. For example, a `Carbon` object could be transformed to `16-05-1994T00:00:00+00`.

A transformer implements the following interface:

```php
interface Transformer
{
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): mixed;
}
```

The following parameters are provided:

- **property**: a `DataProperty` object which represents the property for which the value is transformed. You can read more about the internal structures of the package [here](/docs/laravel-data/v4/advanced-usage/internal-structures)
- **value**: the value that should be transformed, this will never be `null`
- **context**: a `TransformationContext` object which contains the current transformation context with the following properties:
    - **transformValues** indicates if values should be transformed or not
    - **mapPropertyNames** indicates if property names should be mapped or not
    - **wrapExecutionType** the execution type that should be used for wrapping values
    - **transformers** a collection of transformers that can be used to transform values

In the end, the transformer should return a transformed value.

## Combining transformers and casts

You can transformers and casts in one class:

```php
class ToUpperCastAndTransformer implements Cast, Transformer
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): string
    {
        return strtoupper($value);
    }
    
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): string
    {
        return strtoupper($value);
    }
}
```

Within your data object, you can use the `WithCastAndTransformer` attribute to use the cast and transformer:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        #[WithCastAndTransformer(SomeCastAndTransformer::class)]
        public string $artist,
    ) {
    }
}
```
