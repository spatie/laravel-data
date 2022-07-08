<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\RequiringRule;
use Spatie\LaravelData\Support\Validation\RulesCollection;

class RequiredRuleInferrer implements RuleInferrer
{
    public function handle(DataProperty $property, RulesCollection $rules): RulesCollection
    {
        if ($this->shouldAddRule($property, $rules)) {
            $rules->add(new Required());
        }

        return $rules;
    }

    protected function shouldAddRule(DataProperty $property, RulesCollection $rules): bool
    {
        if ($property->type->isNullable || $property->type->isOptional) {
            return false;
        }

        if ($property->type->isDataCollectable && $rules->hasType(Present::class)) {
            return false;
        }

        if ($rules->hasType(BooleanType::class)) {
            return false;
        }

        if ($rules->hasType(Nullable::class)) {
            return false;
        }

        if ($rules->hasType(RequiringRule::class)) {
            return false;
        }

        return true;
    }
}
