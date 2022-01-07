<?php

namespace Spatie\LaravelData\RuleInferrers;

use BackedEnum;
use Illuminate\Validation\Rules\Enum;
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

        if ($enumClass = $property->types()->getImplementedType(BackedEnum::class)) {
            $rules[] = new Enum($enumClass);
        }

        return $rules;
    }
}
