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
