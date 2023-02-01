<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\PropertyRules;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class SometimesRuleInferrer implements RuleInferrer
{
    public function handle(
        DataProperty $property,
        PropertyRules $rules,
        ValidationContext $context,
    ): PropertyRules {
        if ($property->type->isOptional && ! $rules->hasType(Sometimes::class)) {
            $rules->prepend(new Sometimes());
        }

        return $rules;
    }
}
