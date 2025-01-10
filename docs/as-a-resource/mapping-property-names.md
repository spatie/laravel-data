---
title: Mapping property names
weight: 3
---

Sometimes you might want to change the name of a property in the transformed payload, with attributes this is possible:

```php
class ContractData extends Data
{
    public function __construct(
        public string $name,
        #[MapOutputName('record_company')]
        public string $recordCompany,
    ) {
    }
}
```

Now our array looks like this:

```php
[
    'name' => 'Rick Astley',
    'record_company' => 'RCA Records',
]
```

Changing all property names in a data object to snake_case as output data can be done as such:

```php
#[MapOutputName(SnakeCaseMapper::class)]
class ContractData extends Data
{
    public function __construct(
        public string $name,
        public string $recordCompany,
    ) {
    }
}
```

You can also use the `MapName` attribute when you want to combine input and output property name mapping:

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
    'input' => null,
    'output' => SnakeCaseMapper::class,
],
```

You can now create a data object as such:

```php
$contract = new ContractData(
    name: 'Rick Astley',
    recordCompany: 'RCA Records',
);
```

And a transformed version of the data object will look like this:

```php
[
    'name' => 'Rick Astley',
    'record_company' => 'RCA Records',
]
```

### Available mappers:

```php
class ContractData extends Data
{
    public function __construct(
        #[MapName(CamelCaseMapper::class)]
        public string $name,
        #[MapName(SnakeCaseMapper::class)]
        public string $recordCompany,
        #[MapName(new ProvidedNameMapper('country field'))]
        public string $country,
        #[MapName(StudlyCaseMapper::class)]
        public string $cityName,
        #[MapName(LowerCaseMapper::class)]
        public string $addressLine1,
        #[MapName(UpperCaseMapper::class)]
        public string $addressLine2,
    ) {
    }
}
```
```php
[
    'name' => 'Rick Astley',
    'record_company' => 'RCA Records',
    'country field' => 'Belgium',
    'CityName' => 'Antwerp',
    'addressline1' => 'some address line 1',
    'ADDRESSLINE2' => 'some address line 2',
]
```
