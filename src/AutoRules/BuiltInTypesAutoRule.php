<?php

namespace Spatie\LaravelData\AutoRules;

use Spatie\LaravelData\Support\DataProperty;

class BuiltInTypesAutoRule implements AutoRule
{
    public function handle(DataProperty $property, array $rules): array
    {
        if (! $property->isBuiltIn()) {
            return $rules;
        }

        if (in_array('int', $property->types())) {
            $rules[] = 'numeric';
        }

        if (in_array('string', $property->types())) {
            $rules[] = 'string';
        }

        if (in_array('bool', $property->types())) {
            $rules[] = 'boolean';
        }

        if (in_array('float', $property->types())) {
            $rules[] = 'numeric';
        }

        if (in_array('array', $property->types())) {
            $rules[] = 'array';
        }

        return $rules;
    }
}
