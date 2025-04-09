---
title: Mapping property names
weight: 7
---

Sometimes the property names in the array from which you're creating a data object might be different. You can define another name for a property when it is created from an array using attributes:

```php
class ContractData extends Data
{
    public function __construct(
        public string $name,
        #[MapInputName('record_company')]
        public string $recordCompany,
    ) {
    }
}
```

Creating the data object can now be done as such:

```php
ContractData::from(['name' => 'Rick Astley', 'record_company' => 'RCA Records']);
```

Changing all property names in a data object to snake_case in the data the object is created from can be done as such:

```php
#[MapInputName(SnakeCaseMapper::class)]
class ContractData extends Data
{
    public function __construct(
        public string $name,
        public string $recordCompany,
    ) {
    }
}
```

You can also use the `MapName` attribute when you want to combine input (see [transforming data objects](/docs/laravel-data/v4/as-a-resource/mapping-property-names)) and output property name mapping:

```php
#[MapName(SnakeCaseMapper::class)]
class ContractData extends Data
{
    public function __construct(
        public string $name,
        public string $recordCompany,
    ) {
    }
}
```

It is possible to set a default name mapping strategy for all data objects in the `data.php` config file:

```php
'name_mapping_strategy' => [
    'input' => SnakeCaseMapper::class,
    'output' => null,
],
```


## Mapping Nested Properties

You can also map nested properties using dot notation in the `MapInputName` attribute. This is useful when you want to extract a nested value from an array and assign it to a property in your data object:

```php
class SongData extends Data
{
    public function __construct(
        #[MapInputName("title.name")]
        public string $title,
        #[MapInputName("artists.0.name")]
        public string $artist
    ) {
    }
}
```

You can create the data object from an array with nested structures:

```php
SongData::from([
    "title" => [
        "name" => "Never gonna give you up"
    ],
    "artists" => [
        ["name" => "Rick Astley"]
    ]
]);
```

The package has a set of default mappers available, you can find them [here](/docs/laravel-data/v4/advanced-usage/available-property-mappers).
