---
title: Creating a cast
weight: 6
---

Casts take simple values and cast them into complex types. For example, `16-05-1994T00:00:00+00` could be cast into a `Carbon` object with the same date.

A cast implements the following interface:

```php
interface Cast
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed;
}
```

A cast receives the following:

- **property** a `DataProperty` object which represents the property for which the value is cast. You can read more about the internal structures of the package [here](/docs/laravel-data/v4/advanced-usage/internal-structures)
- **value** the value that should be cast
- **properties** an array of the current properties that will be used to create the data object
- **creationContext** the context in which the data object is being created you'll find the following info here:
    - **dataClass** the data class which is being created
    - **validationStrategy** the validation strategy which is being used
    - **mapPropertyNames** whether property names should be mapped
    - **disableMagicalCreation** whether to use the magical creation methods or not
    - **ignoredMagicalMethods** the magical methods which are ignored
    - **casts** a collection of global casts

In the end, the cast should return a casted value.

When the cast is unable to cast the value, an `Uncastable` object should be returned.

## Null

A cast like a transformer never receives a `null` value, this is because the package will always keep a `null` value as `null` because we don't want to create values out of thin air. If you want to replace a `null` value, then use a magic method.

## Castables

You may want to allow your application's value objects to define their own custom casting logic. Instead of attaching the custom cast class to your object, you may alternatively attach a value object class that implements the `Spatie\LaravelData\Casts\Castable` interface:

```php
class ForgotPasswordRequest extends Data
{
    public function __construct(
        #[WithCastable(Email::class)]
        public Email $email,
    ) {
    }
}
```

When using `Castable` classes, you may still provide arguments in the `WithCastable` attribute. The arguments will be passed to the `dataCastUsing` method:

```php
class DuplicateEmailCheck extends Data
{
    public function __construct(
        #[WithCastable(Email::class, normalize: true)]
        public Email $email,
    ) {
    }
}
```

By combining "castables" with PHP's [anonymous classes](https://www.php.net/manual/en/language.oop5.anonymous.php), you may define a value object and its casting logic as a single castable object. To accomplish this, return an anonymous class from your value object's `dataCastUsing` method. The anonymous class should implement the `Cast` interface:

```php
<?php
namespace Spatie\LaravelData\Tests\Fakes\Castables;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Castable;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class Email implements Castable
{
  public function __construct(public string $email) {

  }

  public static function dataCastUsing(...$arguments): Cast
  {
    return new class implements Cast {
        public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
        {
            return new Email($value);
        }
    };
  }
}
```

## Casting iterable values

We saw earlier that you can cast all sorts of values in an array or Collection which are not data objects, for this to work, you should implement the `IterableItemCast` interface:

```php
interface IterableItemCast
{
    public function castIterableItem(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed;
}
```

The `castIterableItem` method is called for each item in an array or Collection when being cast, you can check the `iterableItemType` property of `DataProperty->type` to get the type the items should be transformed into.

## Combining casts and transformers

You can combine casts and transformers in one class:

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
