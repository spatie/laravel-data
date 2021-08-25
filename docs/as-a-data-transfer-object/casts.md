---
title: Casts
weight: 2
---

We extend our data object just a little bit:

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

The `Format` property here is an `Enum` from our [enum package](https://github.com/spatie/enum) that looks like this:

```php
/**
 * @method static self cd()
 * @method static self vinyl()
 * @method static self cassette()
 */
class Format extends Enum{
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

We get an error, that's because the first 2 properties are simple PHP types(string, int's, floats, booleans, arrays) but the next two properties are more complex types a `DateTime` and `Enum` respectively. These types cannot be automatically created, a cast is needed to construct them from a string.

There are two types of casts, local and global casts. 

## local casts

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

### Casting dates

By default the package ships with a `DateTimeInterface` cast that can be used to transform date string into date objects. The format used to parse the date can be defined in the `data.php` config file. Or you can manually provide it on the cast:

```php
#[WithCast(DateTimeInterfaceCast::class, format: DATE_ATOM)]
public DateTime $date
```

The type of the property will be used to cast a date string into, so if you want to use `Carbon` that's perfectly possible:

```php
#[WithCast(DateTimeInterfaceCast::class)]
public Carbon $date
```

You can even manually specify the type the date string should be casted to:

```php

#[WithCast(DateTimeInterfaceCast::class, type: CarbonImmutable::class)]
public $date
```

## Global casts

Global casts are not defined on the data object but in your `data.php` config file:

```php
'casts' => [
    DateTimeInterface::class => Spatie\LaravelData\Casts\DateTimeInterfaceCast::class,
],
```

When no local cast can be found for the property, the package will look through the global casts and tries to find a suitable cast. You can define casts for:

- a **specific implementation** (e.g. CarbonImmutable)
- an **interface** (e.g. DateTimeInterface)
- a **base class** (e.g. Enum)

As you can see, the package by default already provides a `DateTimeInterface` cast, so we can update our data object like this:

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

## Creating your own casts

It is possible to create your own casts you can read more about this in the advanced chapter.

