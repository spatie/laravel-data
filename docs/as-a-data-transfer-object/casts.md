---
title: Casts
weight: 5
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

And get an error because the first two properties are simple PHP types(strings, ints, floats, booleans, arrays), but the following two properties are more complex types: `DateTime` and `Enum`, respectively.

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

## Casting arrays or collections of non-data types

We've already seen how collections of data can be made of data objects, the same is true for all other types if correctly
typed.

Let say we have an array of DateTime objects:

```php
class ReleaseData extends Data
{
    public string $title;
    /** @var array<int, DateTime> */
    public array $releaseDates;
}
```

By enabling the `cast_and_transform_iterables` feature in the `data` config file (this feature will be enabled by default in laravel-data v5):

```php
'features' => [
    'cast_and_transform_iterables' => true,
],
```

We now can create a `ReleaseData` object with an array of strings which will be cast into an array DateTime objects:

```php
ReleaseData::from([
    'title' => 'Never Gonna Give You Up',
    'releaseDates' => [
        '1987-07-27T12:00:00Z',
        '1987-07-28T12:00:00Z',
        '1987-07-29T12:00:00Z',
    ],
]);
```

For this feature to work, a cast should not only implement the `Cast` interface but also the `IterableItemCast`. The
signatures of the `cast` and `castIterableItem` methods are exactly the same, but they're called on different times.
When casting a property like a DateTime from a string, the `cast` method will be used, when transforming an iterable
property like an array or Laravel Collection where the iterable item is typed using an annotation, then each item of the
provided iterable will trigger a call to the `castIterableItem` method.
