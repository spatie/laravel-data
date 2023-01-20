<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\PropertyRules;
use Spatie\LaravelData\Support\Validation\RulesToValidationRule;
use Spatie\LaravelData\Support\Validation\ValidationPath;
use Spatie\LaravelData\Support\Validation\ValidationRule;

class AttributesRuleInferrer implements RuleInferrer
{
    public function __construct(protected RulesToValidationRule $rulesDenormalizer)
    {
    }

    public function handle(
        DataProperty $property,
        PropertyRules $rules,
        ValidationPath $path,
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
