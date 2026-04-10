---
title: Computed values
weight: 9
---

Earlier we saw how default values can be set for a data object, sometimes you want to set a default value based on other properties. For example, you might want to set a `full_name` property based on a `first_name` and `last_name` property. You can do this by using a computed property:

```php
use Spatie\LaravelData\Attributes\Computed;

class SongData extends Data
{
    #[Computed]
    public string $full_name;

    public function __construct(
        public string $first_name,
        public string $last_name,
    ) {
        $this->full_name = "{$this->first_name} {$this->last_name}";
    }
}
```

You can now do the following:

```php
SongData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche']);
```

Please notice: the computed property won't be reevaluated when its dependencies change. If you want to update a computed property, you'll have to create a new object.

Again there are a few conditions for this approach:

- You must always use a sole property, a property within the constructor definition won't work
- Computed properties cannot be defined in the payload, a `CannotSetComputedValue` will be thrown if this is the case
- If the `ignore_exception_when_trying_to_set_computed_property_value` configuration option is set to `true`, the computed property will be silently ignored when trying to set it in the payload and no `CannotSetComputedValue` exception will be thrown.

## PHP 8.4 virtual properties

If you're using PHP 8.4 or higher, you can use [property hooks](https://www.php.net/manual/en/language.oop5.property-hooks.php) to define virtual properties. These are automatically detected as computed properties without needing the `#[Computed]` attribute:

```php
class SongData extends Data
{
    public string $full_name {
        get => "{$this->first_name} {$this->last_name}";
    }

    public function __construct(
        public string $first_name,
        public string $last_name,
    ) {
    }
}
```

Virtual properties behave exactly like `#[Computed]` properties: they cannot be set from the payload and are excluded from serialization. Backed properties with hooks (properties that have a get hook but also store a value) are not treated as computed.
