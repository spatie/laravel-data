<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\RulesCollection;

class NullableRuleInferrer implements RuleInferrer
{
    public function handle(
        DataProperty $property,
        RulesCollection $rules,
        ?string $path,
    ): RulesCollection {
        if ($property->type->isNullable && ! $rules->hasType(Nullable::class)) {
            $rules->add(new Nullable());
        }

        return $rules;
    }
}
