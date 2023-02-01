<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\PropertyRules;
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
        $property
            ->attributes
            ->filter(fn (object $attribute) => $attribute instanceof ValidationRule)
            ->each(function (ValidationRule $rule) use ($rules) {
                $rules->add(
                    ...$this->rulesDenormalizer->execute($rule)
                );
            });

        return $rules;
    }
}
