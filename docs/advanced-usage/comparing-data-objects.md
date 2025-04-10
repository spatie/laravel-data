---
title: Comparing data objects
weight: 20
---

## Comparing data objects

The package provides a way to compare data objects with each other using the `ComparableData` trait and interface. This is helpful when you need to determine if two data objects have the same values, regardless of whether they are the same instance.

The `equalTo` method compares two data objects by comparing their array representations. This means that two data objects are considered equal if their `toArray()` method returns the same array.

Both the `Spatie\LaravelData\Data` and `Spatie\LaravelData\Resource` classes implement the `ComparableData` interface and use the `ComparableData` trait.

### Basic usage

You can use the `equalTo` method to compare two data objects:

```php
class UserData extends Data
{
    public function __construct(
        public int $id,
        public string|Optional $name = new Optional,
    ) {}
}

$data1 = new UserData(id: 10);
$data2 = UserData::from($data1);

// Default PHP comparison
$data1 == $data2; // false

// New value comparison
$data1->equalTo($data2); // true
```
