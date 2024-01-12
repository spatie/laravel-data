---
title: Creating a cast
weight: 8
---

Casts take simple values and cast them into complex types. For example, `16-05-1994T00:00:00+00` could be cast into a `Carbon` object with the same date.

A cast implements the following interface:

```php
interface Cast
{
    public function cast(DataProperty $property, mixed $value, array $context): mixed;
}
```

The value that should be cast is given, and a `DataProperty` object which represents the property for which the value is cast. You can read more about the internal structures of the package [here](/docs/laravel-data/v3/advanced-usage/internal-structures).

Within the `context` array the complete payload is given.

In the end, the cast should return a casted value. Please note that the given value of a cast can never be `null`.

When the cast is unable to cast the value, an `Uncastable` object should be returned.

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
use Spatie\LaravelData\Support\DataProperty;

class Email implements Castable
{
  public function __construct(public string $email) {

  }

  public static function dataCastUsing(...$arguments): Cast
  {
    return new class implements Cast {
        public function cast(DataProperty $property, mixed $value, array $context): mixed {
            return new Email($value);
        }
    };
  }
}
```
