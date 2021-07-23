<?php

namespace Spatie\LaravelData\AutoRules;

use Spatie\LaravelData\Support\DataProperty;

class RequiredAutoRule implements AutoRule
{
    public function handle(DataProperty $property, array $rules): array
    {
        if(! $property->isNullable()){
            $rules[] = 'required';
        }

        return $rules;
    }
}
