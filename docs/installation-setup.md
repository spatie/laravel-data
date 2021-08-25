---
title: Installation & setup
weight: 4
---

You can install the package via composer:

```bash
composer require spatie/laravel-data
```

Optionally, You can publish the config file with:

```bash
php artisan vendor:publish --provider="Spatie\LaravelData\LaravelDataServiceProvider" --tag="data-config"
```

This is the contents of the published config file:

```php
return [
    /*
     * The date format to be used when converting a DateTimeInterface from and to json
     */
    'date_format' => DATE_ATOM,

    /*
     * Transformers will take properties within your data objects and transform
     * them to types that can be JSON encoded.
     */
    'transformers' => [
        \Spatie\LaravelData\Transformers\DateTransformer::class,
        \Spatie\LaravelData\Transformers\ArrayableTransformer::class,
    ],

    /*
     * Global casts will automatically transform values in arrays to data object properties
     */
    'casts' => [
        DateTimeInterface::class => Spatie\LaravelData\Casts\DateTimeInterfaceCast::class,
    ],

    /*
     * Validation Rules that will automatically be added when a data object is resolved
     * from a request
     */
    'rule_inferrers' => [
        Spatie\LaravelData\RuleInferrers\NullableRuleInferrer::class,
        Spatie\LaravelData\RuleInferrers\RequiredRuleInferrer::class,
        Spatie\LaravelData\RuleInferrers\BuiltInTypesRuleInferrer::class,
        Spatie\LaravelData\RuleInferrers\AttributesRuleInferrer::class,
    ],
];
```

