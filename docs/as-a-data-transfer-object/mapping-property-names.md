---
title: Mapping property names
weight: 6
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
