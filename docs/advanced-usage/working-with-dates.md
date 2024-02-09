---
title: Working with dates
weight: 3
---

Dates can be hard, there are tons of formats to cast them from or transform them to. Within the `data.php` config file a
default date format can be set:

```php
    'date_format' => DATE_ATOM,
```

Now when using the `DateTimeInterfaceCast` or `DateTimeInterfaceTransformer` the format defined will be used

```php
#[WithCast(DateTimeInterfaceCast::class)]
#[WithTransformer(DateTimeInterfaceTransformer::class)]
public DateTime $date
```

It is also possible to manually set the format;

```php
#[WithCast(DateTimeInterfaceCast::class, format: DATE_ATOM)]
#[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
public DateTime $date
```

When casting the data object will use the type of the property to cast a date string into, so if you want to
use `Carbon`, that's perfectly possible:

```php
#[WithCast(DateTimeInterfaceCast::class)]
public Carbon $date
```

You can even manually specify the type the date string should be cast to:

```php

#[WithCast(DateTimeInterfaceCast::class, type: CarbonImmutable::class)]
public $date
```

## Multiple date formats

Sometimes your application might use different date formats, for example, you receive dates from an IOS and React
application. These use different underlying date formats. In such case you can add an array to the `date_format` key
within the `data.php` config file:

```php
    'date_format' => [DATE_ATOM, 'Y-m-d'],
```

Now when casting a date, a valid format will be searched. When none can be found, an exception is thrown.

When a transformers hasn't explicitly stated its format, the first format within the array is used.

## Casting dates in a different time zone

Sometimes a date can be in a different timezone than the timezone you application uses. For example, if your application uses `Europe/Brussels` but your date is in `UTC`:

```php
#[WithCast(DateTimeInterfaceCast::class, timeZone: 'UTC')]
public DateTime $date
```

The date will be created with the `UTC` timezone but will be the same as in the `Europe/Brussels` timezone.

## Changing time zones

When casting a date you may want to set an alternative timezone this can be achieved as such:

```php
#[WithCast(DateTimeInterfaceCast::class, setTimeZone: 'Europe/Brussels')]
public DateTime $date
```

In this case the time will be transformed, if our original time was in `UTC` then one or two hours (depending on summer time) will be added.

You can also change the timezone of a property which is getting transformed:

```php
#[WithTransformer(DateTimeInterfaceTransformer::class, setTimeZone: 'Europe/Brussels')]
public DateTime $date
```
