<?php

namespace Spatie\LaravelData\AutoRules;

use Spatie\LaravelData\Attributes\DataValidationAttribute;
use Spatie\LaravelData\Support\DataProperty;

class AttributesAutoRule implements AutoRule
{
    public function handle(DataProperty $property, array $rules): array
    {
        $attributeRules = array_map(
            fn(DataValidationAttribute $attribute) => $attribute->getRules(),
            $property->validationAttributes()
        );

        return array_merge($rules, ...$attributeRules);
    }
}
