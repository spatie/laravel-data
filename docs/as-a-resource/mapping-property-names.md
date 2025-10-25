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

The package has a set of default mappers available, you can find them [here](/docs/laravel-data/v4/advanced-usage/available-property-mappers).

## Expanding Dotted Notation

By default, dotted notation in property names (e.g., 'user.name') is kept as-is in the output. You can enable expansion to create nested arrays using **four different approaches**:

```php
class UserData extends Data
{
    public function __construct(
        // 1. MapDotExpandedOutputName - output only, cleaner syntax
        #[MapDotExpandedOutputName('user.profile.name')]
        public string $name,
        
        // 2. MapDotExpandedName - input & output, cleaner syntax
        #[MapDotExpandedName('user.profile.email')]
        public string $email,
        
        // 3. MapOutputName with parameter - output only, explicit
        #[MapOutputName('user.settings.theme', expandDotNotation: true)]
        public string $theme,
        
        // 4. MapName with parameter - input & output, explicit
        #[MapName('user.settings.language', expandDotNotation: true)]
        public string $language,
    ) {
    }
}
```

All four approaches transform to nested arrays:

```php
[
    'user' => [
        'profile' => [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ],
        'settings' => [
            'theme' => 'dark',
            'language' => 'en',
        ],
    ],
]
```

**Choosing the right attribute:**
- Use `MapDotExpandedOutputName` or `MapOutputName(..., expandDotNotation: true)` for output-only transformation
- Use `MapDotExpandedName` or `MapName(..., expandDotNotation: true)` for both input mapping and output transformation
- Dedicated attributes provide cleaner syntax; parameter-based approach offers more flexibility
