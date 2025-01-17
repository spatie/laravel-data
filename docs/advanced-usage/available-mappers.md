---
title: Available mappers
weight: 19
---

Sometimes the property names in the array from which you're creating a data object might be different.
You can use mappers for property names when it is created from an array using attributes:

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

Creating the data object can now be done as such:

```php
ContractData::from([
    'name' => 'Rick Astley',
    'record_company' => 'RCA Records',
    'country field' => 'Belgium',
    'CityName' => 'Antwerp',
    'addressline1' => 'some address line 1',
    'ADDRESSLINE2' => 'some address line 2',
]);
```
