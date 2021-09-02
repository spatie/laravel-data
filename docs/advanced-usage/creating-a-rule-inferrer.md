---
title: Creating a rule inferrer
weight: 4
---

Rule inferrers will try to infer validation rules for properties within a data object.

A rule inferrer can be created by implementing the `RuleInferrer` interface:

```php
interface RuleInferrer
{
    public function handle(DataProperty $property, array $rules): array;
}
```

An array of previous inferred rules is given and a `DataProperty` object that represents the property for which the value is transformed. You can read more about the internal structures of the package [here](TODO).

In a rule inferrer it is possible to merge, remove or add new rules to the rules inferred by other rule inferrers. In the end a rule inferrer should always return an array of rules.

Rule inferrers need to be manually defined within the `data.php` config file.

