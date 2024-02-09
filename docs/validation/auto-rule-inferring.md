---
title: Auto rule inferring
weight: 2
---

The package will automatically infer validation rules from the data object. For example, for the following data class:

```php
class ArtistData extends Data{
    public function __construct(
        public string $name,
        public int $age,
        public ?string $genre,
    ) {
    }
}
```

The package will generate the following validation rules:

```php
[
    'name' => ['required', 'string'],
    'age' => ['required', 'integer'],
    'genre' => ['nullable', 'string'],
]
```

All these rules are inferred by `RuleInferrers`, special classes that will look at the types of properties and will add rules based upon that.

Rule inferrers are configured in the `data.php` config file:

```php
/*
 * Rule inferrers can be configured here. They will automatically add
 * validation rules to properties of a data object based upon
 * the type of the property.
 */
'rule_inferrers' => [
    Spatie\LaravelData\RuleInferrers\SometimesRuleInferrer::class,
    Spatie\LaravelData\RuleInferrers\NullableRuleInferrer::class,
    Spatie\LaravelData\RuleInferrers\RequiredRuleInferrer::class,
    Spatie\LaravelData\RuleInferrers\BuiltInTypesRuleInferrer::class,
    Spatie\LaravelData\RuleInferrers\AttributesRuleInferrer::class,
],
```

By default, five rule inferrers are enabled:

- **SometimesRuleInferrer** will add a `sometimes` rule when the property is optional
- **NullableRuleInferrer** will add a `nullable` rule when the property is nullable
- **RequiredRuleInferrer** will add a `required` rule when the property is not nullable
- **BuiltInTypesRuleInferrer** will add a rules which are based upon the built-in php types:
    - An `int` or `float` type will add the `numeric` rule
    - A `bool` type will add the `boolean` rule
    - A `string` type will add the `string` rule
    - A `array` type will add the `array` rule
- **AttributesRuleInferrer** will make sure that rule attributes we described above will also add their rules

It is possible to write your rule inferrers. You can find more information [here](/docs/laravel-data/v4/advanced-usage/creating-a-rule-inferrer).
