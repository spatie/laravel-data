<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Support\DataProperty;

class SometimesRuleInferrer implements RuleInferrer
{
    public function handle(DataProperty $property, array $rules): array
    {
        if ($property->type->isOptional && ! in_array('sometimes', $rules)) {
            $rules[] = 'sometimes';
        }

        return $rules;
    }
}
