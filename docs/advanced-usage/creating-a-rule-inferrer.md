---
title: Creating a rule inferrer
weight: 8
---

Rule inferrers will try to infer validation rules for properties within a data object.

A rule inferrer can be created by implementing the `RuleInferrer` interface:

```php
interface RuleInferrer
{
    public function handle(DataProperty $property, array $rules): array;
}
```

An array of previous inferred rules is given, and a `DataProperty` object which represents the property for which the value is transformed. You can read more about the internal structures of the package [here](/docs/laravel-data/v2/advanced-usage/internal-structures).

It is possible to merge, remove, or add new rules to the rules inferred by other rule inferrers in a rule inferrer. In the end, a rule inferrer should always return an array of rules.

Rule inferrers need to be manually defined within the `data.php` config file.

