<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Support\DataProperty;

class BuiltInTypesRuleInferrer implements RuleInferrer
{
    public function handle(DataProperty $property, array $rules): array
    {
        if (! $property->isBuiltIn()) {
            return $rules;
        }

        if ($property->types()->canBe('int')) {
            $rules[] = 'numeric';
        }

        if ($property->types()->canBe('string')) {
            $rules[] = 'string';
        }

        if ($property->types()->canBe('bool')) {
            $rules[] = 'boolean';
        }

        if ($property->types()->canBe('float')) {
            $rules[] = 'numeric';
        }

        if ($property->types()->canBe('array')) {
            $rules[] = 'array';
        }

        return $rules;
    }
}
