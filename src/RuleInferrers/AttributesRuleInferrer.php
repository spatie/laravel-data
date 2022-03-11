<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Attributes\Validation\ValidationAttribute;
use Spatie\LaravelData\Support\DataProperty;

class AttributesRuleInferrer implements RuleInferrer
{
    public function handle(DataProperty $property, array $rules): array
    {
        $attributeRules = $property->attributes
            ->filter(fn (object $attribute) => $attribute instanceof ValidationAttribute)
            ->map(fn(ValidationAttribute $attribute) => $attribute->getRules())
            ->all();


        return array_merge($rules, ...$attributeRules);
    }
}
