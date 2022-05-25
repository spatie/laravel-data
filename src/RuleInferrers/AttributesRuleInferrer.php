<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Support\Validation\RulesCollection;
use Spatie\LaravelData\Support\Validation\ValidationRule;
use Spatie\LaravelData\Support\DataProperty;

class AttributesRuleInferrer implements RuleInferrer
{
    public function handle(DataProperty $property, RulesCollection $rules): RulesCollection
    {
        $property->attributes
            ->filter(fn(object $attribute) => $attribute instanceof ValidationRule)
            ->each(fn(ValidationRule $rule) => $rules->add($rule));

        return $rules;
    }
}
