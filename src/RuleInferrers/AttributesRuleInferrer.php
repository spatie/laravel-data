<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Attributes\Validation\ValidationAttribute;
use Spatie\LaravelData\Support\DataProperty;

class AttributesRuleInferrer implements RuleInferrer
{
    public function handle(DataProperty $property, array $rules): array
    {
        $attributeRules = array_map(
            fn (ValidationAttribute $attribute) => $attribute->getRules(),
            $property->validationAttributes
        );

        return array_merge($rules, ...$attributeRules);
    }
}
