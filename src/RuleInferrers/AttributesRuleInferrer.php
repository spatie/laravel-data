<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Attributes\Validation\Present;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\PropertyRules;
use Spatie\LaravelData\Support\Validation\RequiringRule;
use Spatie\LaravelData\Support\Validation\RuleNormalizer;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\LaravelData\Support\Validation\ValidationRule;

class AttributesRuleInferrer implements RuleInferrer
{
    public function __construct(protected RuleNormalizer $rulesDenormalizer)
    {
    }

    public function handle(
        DataProperty $property,
        PropertyRules $rules,
        ValidationContext $context,
    ): PropertyRules {
        foreach ($property->attributes->all(ValidationRule::class) as $rule) {
            if ($rule instanceof Present && $rules->hasType(RequiringRule::class)) {
                $rules->removeType(RequiringRule::class);
            }

            if ($rule instanceof RequiringRule && $rules->hasType(Sometimes::class)) {
                $rules->removeType(Sometimes::class);
            }

            $rules->add(
                ...$this->rulesDenormalizer->execute($rule)
            );
        }

        return $rules;
    }
}
