---
title: Casts
weight: 4
---

We extend our example data object just a little bit:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
        public DateTime $date,
        public Format $format,
    ) {
    }
}
```

The `Format` property here is an `Enum` and looks like this:

```php
enum Format: string {
    case cd = 'cd';
    case vinyl = 'vinyl';
    case cassette = 'cassette';
}
```

When we now try to construct a data object like this:

```php
SongData::from([
    'title' => 'Never gonna give you up',
    'artist' => 'Rick Astley',
    'date' => '27-07-1987',
    'format' => 'vinyl',
]);
```

And get an error because the first two properties are simple PHP types(string, int's, floats, booleans, arrays), but the following two properties are more complex types: `DateTime` and `Enum`, respectively.

These types cannot be automatically created. A cast is needed to construct them from a string.

There are two types of casts, local and global casts.

## Local casts

Local casts are defined within the data object itself and can be added using attributes:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
        #[WithCast(DateTimeInterfaceCast::class)]
        public DateTime $date,
        #[WithCast(EnumCast::class)]
        public Format $format,
    ) {
    }
}
```

Now it is possible to create a data object like this without exceptions:

```php
SongData::from([
    'title' => 'Never gonna give you up',
    'artist' => 'Rick Astley',
    'date' => '27-07-1987',
    'format' => 'vinyl',
]);
```

It is possible to provide parameters to the casts like this:

```php
#[WithCast(EnumCast::class, type: Format::class)]
public Format $format
```

## Global casts

Global casts are not defined on the data object but in your `data.php` config file:

```php
'casts' => [
    DateTimeInterface::class => Spatie\LaravelData\Casts\DateTimeInterfaceCast::class,
],
```

When the data object can find no local cast for the property, the package will look through the global casts and tries to find a suitable cast. You can define casts for:

- a **specific implementation** (e.g. CarbonImmutable)
- an **interface** (e.g. DateTimeInterface)
- a **base class** (e.g. Enum)

As you can see, the package by default already provides a `DateTimeInterface` cast, this means we can update our data object like this:

```php
class SongData extends Data
{
    public function __construct(
        public string $title,
        public string $artist,
        public DateTime $date,
        #[WithCast(EnumCast::class)]
        public Format $format,
    ) {
    }
}
```

Tip: we can also remove the `EnumCast` since the package will automatically cast enums because they're a native PHP type, but this made the example easy to understand.

## Creating your own casts

It is possible to create your casts. You can read more about this in the [advanced chapter](/docs/laravel-data/v4/advanced-usage/creating-a-cast).
