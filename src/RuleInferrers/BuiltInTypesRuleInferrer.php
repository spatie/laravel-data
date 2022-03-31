<?php

namespace Spatie\LaravelData\RuleInferrers;

use BackedEnum;
use Illuminate\Validation\Rules\Enum;
use Spatie\LaravelData\Support\DataProperty;

class BuiltInTypesRuleInferrer implements RuleInferrer
{
    public function handle(DataProperty $property, array $rules): array
    {
        if ($property->type->acceptsType('int')) {
            $rules[] = 'numeric';
        }

        if ($property->type->acceptsType('string')) {
            $rules[] = 'string';
        }

        if ($property->type->acceptsType('bool')) {
            $rules[] = 'boolean';
        }

        if ($property->type->acceptsType('float')) {
            $rules[] = 'numeric';
        }

        if ($property->type->acceptsType('array')) {
            $rules[] = 'array';
        }

        if ($enumClass = $property->type->findAcceptedTypeForClass(BackedEnum::class)) {
            $rules[] = new Enum($enumClass);
        }

        return $rules;
    }
}
