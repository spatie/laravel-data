<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\PropertyRules;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class NullableRuleInferrer implements RuleInferrer
{
    public function handle(
        DataProperty $property,
        PropertyRules $rules,
        ValidationContext $context,
    ): PropertyRules {
        if ($property->type->isNullable && ! $rules->hasType(Nullable::class)) {
            $rules->prepend(new Nullable());
        }

        return $rules;
    }
}
