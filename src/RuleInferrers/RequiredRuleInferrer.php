<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Support\DataProperty;

class RequiredRuleInferrer implements RuleInferrer
{
    public function handle(DataProperty $property, array $rules): array
    {
        if (! $property->isNullable()) {
            $rules[] = 'required';
        }

        return $rules;
    }
}
