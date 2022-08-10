<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\RulesCollection;

class SometimesRuleInferrer implements RuleInferrer
{
    public function handle(DataProperty $property, RulesCollection $rules): RulesCollection
    {
        if ($property->type->isOptional && ! $rules->hasType(Sometimes::class)) {
            $rules->add(new Sometimes());
        }

        return $rules;
    }
}
