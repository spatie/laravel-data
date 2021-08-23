<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Attributes\DataValidationAttribute;
use Spatie\LaravelData\Support\DataProperty;

class AttributesRuleInferrer implements RuleInferrer
{
    public function handle(DataProperty $property, array $rules): array
    {
        $attributeRules = array_map(
            fn (DataValidationAttribute $attribute) => $attribute->getRules(),
            $property->validationAttributes()
        );

        return array_merge($rules, ...$attributeRules);
    }
}
