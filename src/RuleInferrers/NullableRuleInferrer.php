<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Support\DataProperty;

class NullableRuleInferrer implements RuleInferrer
{
    public function handle(DataProperty $property, array $rules): array
    {
        if ($property->isNullable() && ! in_array('nullable', $rules)) {
            $rules[] = 'nullable';
        }

        return $rules;
    }
}
