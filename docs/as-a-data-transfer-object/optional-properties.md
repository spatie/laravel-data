---
title: Optional properties
weight: 6
---

Sometimes you have a data object with properties which shouldn't always be set, for example in a partial API update where you only want to update certain fields. In this case you can make a property `Optional` as such:

```php
use Spatie\LaravelData\Optional;

class SongData extends Data
{
    public function __construct(
        public string $title,
        public string|Optional $artist,
    ) {
    }
}
```

You can now create the data object as such:

```php
SongData::from([
    'title' => 'Never gonna give you up'
]);
```

The value of `artist` will automatically be set to `Optional`. When you transform this data object to an array, it will look like this:

```php
[
    'title' => 'Never gonna give you up'
]
```

You can manually use `Optional` values within magical creation methods as such:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string|Optional $artist,
    ) {
    }
    
    public static function fromTitle(string $title): static
    {
        return new self($title, Optional::create());
    }
}
```

It is possible to automatically update `Optional` values to `null`:

```php
class SongData extends Data {
    public function __construct(
        public string $title,
        public Optional|null|string $artist,
    ) {
    }
}

SongData::factory()
    ->withoutOptionalValues()
    ->from(['title' => 'Never gonna give you up']); // artist will `null` instead of `Optional`
```

You can read more about this [here](/docs/laravel-data/v4/as-a-data-transfer-object/factories#disabling-optional-values).
