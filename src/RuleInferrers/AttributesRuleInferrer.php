<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Resolvers\RuleAttributesResolver;
use Spatie\LaravelData\Support\Validation\RulesCollection;
use Spatie\LaravelData\Support\Validation\ValidationRule;
use Spatie\LaravelData\Support\DataProperty;

class AttributesRuleInferrer implements RuleInferrer
{
    public function __construct(private RuleAttributesResolver $ruleAttributesResolver)
    {
    }

    public function handle(DataProperty $property, RulesCollection $rules): RulesCollection
    {
        $property
            ->attributes
            ->filter(fn(object $attribute) => $attribute instanceof ValidationRule)
            ->each(function (ValidationRule $rule) use ($rules) {
                if (! $rule instanceof Rule) {
                    $rules->add($rule);

                    return;
                }

                $rules->add(
                    ...$this->ruleAttributesResolver->execute($rule->getRules())
                );
            });

        return $rules;
    }
}
