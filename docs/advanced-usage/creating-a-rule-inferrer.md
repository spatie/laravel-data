---
title: Creating a rule inferrer
weight: 8
---

Rule inferrers will try to infer validation rules for properties within a data object.

A rule inferrer can be created by implementing the `RuleInferrer` interface:

```php
interface RuleInferrer
{
    public function handle(DataProperty $property, PropertyRules $rules, ValidationContext $context): PropertyRules;
}
```

A collection of previous inferred rules is given, and a `DataProperty` object which represents the property for which the value is transformed. You can read more about the internal structures of the package [here](/docs/laravel-data/v4/advanced-usage/internal-structures).

The `ValidationContext` is also injected, this contains the following info:

- **payload** the current payload respective to the data object which is being validated
- **fullPayload** the full payload which is being validated
- **validationPath** the path from the full payload to the current payload

The `RulesCollection` contains all the rules for the property represented as `ValidationRule` objects.

You can add new rules to it:

```php
$rules->add(new Min(42));
```

When adding a rule of the same kind, a previous version of the rule will be removed:

```php
$rules->add(new Min(42));
$rules->add(new Min(314)); 

$rules->all(); // [new Min(314)]
```

Adding a string rule can be done as such:

```php
$rules->add(new Rule('min:42'));
```

You can check if the collection contains a type of rule:

```php
$rules->hasType(Min::class);
```

Or remove certain types of rules:

```php
$rules->removeType(Min::class);
```

In the end, a rule inferrer should always return a `RulesCollection`.

Rule inferrers need to be manually defined within the `data.php` config file.

