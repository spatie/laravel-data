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

When using `MapOutputName` or `MapName` with dot notation (e.g., 'user.name'), by default the package will keep the dotted notation as-is in the output:

```php
class UserData extends Data
{
    public function __construct(
        #[MapOutputName('user.name')]
        public string $name,
    ) {
    }
}
```

When transformed, this will result in:

```php
[
    'user.name' => 'John Doe',
]
```

However, you can enable the expansion of dotted notation to create nested arrays by setting the `expand_dot_notation` feature flag to `true` in your `config/data.php` file:

```php
'features' => [
    // Other features...
    'expand_dot_notation' => true,
],
```

With this feature enabled, the same data object will transform to:

```php
[
    'user' => [
        'name' => 'John Doe',
    ],
]
```

This feature is particularly useful when you need to generate nested JSON structures from flat data objects or when integrating with APIs that expect nested data.

### Example with Nested Data

Consider a more complex example:

```php
class OrderData extends Data
{
    public function __construct(
        #[MapOutputName('order.id')]
        public int $id,
        #[MapOutputName('order.customer.name')]
        public string $customerName,
        #[MapOutputName('order.items')]
        public array $items,
    ) {
    }
}
```

With `expand_dot_notation` disabled (default):

```php
[
    'order.id' => 1234,
    'order.customer.name' => 'Jane Smith',
    'order.items' => ['item1', 'item2'],
]
```

With `expand_dot_notation` enabled:

```php
[
    'order' => [
        'id' => 1234,
        'customer' => [
            'name' => 'Jane Smith',
        ],
        'items' => ['item1', 'item2'],
    ],
]
```

This feature uses Laravel's `Arr::set()` method internally to expand the dotted notation into nested arrays.
