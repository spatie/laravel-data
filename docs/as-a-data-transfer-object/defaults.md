---
title: Default values
weight: 8
---

There are a few ways to define default values for a data object. Since a data object is just a regular PHP class, you can use the constructor to set default values:

```php
class SongData extends Data
{
    public function __construct(
        public string $title = 'Never Gonna Give You Up',
        public string $artist = 'Rick Astley',
    ) {
    }
}
```

This works for simple types like strings, integers, floats, booleans, enums and arrays. But what if you want to set a default value for a more complex type like a `CarbonImmutable` object? You can use the constructor to do this:

```php
class SongData extends Data
{
    #[Date]
    public CarbonImmutable|Optional $date;

    public function __construct(
        public string $title = 'Never Gonna Give You Up',
        public string $artist = 'Rick Astley',
    ) {
        $this->date = CarbonImmutable::create(1987, 7, 27);
    }
}
```

You can now do the following:

```php
SongData::from();
SongData::from(['title' => 'Giving Up On Love', 'date' => CarbonImmutable::create(1988, 4, 15)]);
```

Even validation will work:

```php
SongData::validateAndCreate();
SongData::validateAndCreate(['title' => 'Giving Up On Love', 'date' => CarbonImmutable::create(1988, 4, 15)]);
```

There are a few conditions for this approach:

- You must always use a sole property, a property within the constructor definition won't work
- The optional type is technically not required, but it's a good idea to use it otherwise the validation won't work
- Validation won't be performed on the default value, so make sure it is valid
