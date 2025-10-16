---
title: Available property mappers
weight: 19
---

In previous sections we've already seen how  to [create](/docs/laravel-data/v4/as-a-data-transfer-object/mapping-property-names) data objects where the keys of the
payload differ from the property names of the data object. It is also possible to [transform](/docs/laravel-data/v4/as-a-resource/mapping-property-names) data objects to an array/json/... where the keys of the payload differ 
from the property names of the data object.

These mappings can be set manually put the package also provide a set of mappers that can be used to automatically map
property names:

```php
use Spatie\LaravelData\Mappers\CamelCaseMapper;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Mappers\KebabCaseMapper;
use Spatie\LaravelData\Mappers\ProvidedNameMapper;
use Spatie\LaravelData\Mappers\StudlyCaseMapper;
use Spatie\LaravelData\Mappers\LowerCaseMapper;
use Spatie\LaravelData\Mappers\UpperCaseMapper;

class ContractData extends Data
{
    public function __construct(
        #[MapName(CamelCaseMapper::class)]
        public string $name,
        #[MapName(SnakeCaseMapper::class)]
        public string $recordCompany,
        #[MapName(KebabCaseMapper::class)]
        public string $vatNumber,
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

When transforming such a data object the payload will look like this:

```json
{
    "name" : "Rick Astley",
    "record_company" : "RCA Records",
    "country field" : "Belgium",
    "CityName" : "Antwerp",
    "addressline1" : "some address line 1",
    "ADDRESSLINE2" : "some address line 2"
}
```
