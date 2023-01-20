<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\PropertyRules;
use Spatie\LaravelData\Support\Validation\ValidationPath;

class SometimesRuleInferrer implements RuleInferrer
{
    public function handle(
        DataProperty $property,
        PropertyRules $rules,
        ValidationPath $path,
    ): PropertyRules {
        if ($property->type->isOptional && ! $rules->hasType(Sometimes::class)) {
            $rules->prepend(new Sometimes());
        }

        return $rules;
    }
}
