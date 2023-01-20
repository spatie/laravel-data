<?php

namespace Spatie\LaravelData\RuleInferrers;

use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Validation\PropertyRules;
use Spatie\LaravelData\Support\Validation\RulesMapper;
use Spatie\LaravelData\Support\Validation\ValidationPath;
use Spatie\LaravelData\Support\Validation\ValidationRule;

class AttributesRuleInferrer implements RuleInferrer
{
    public function __construct(protected RulesMapper $ruleAttributesResolver)
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
            ->each(function (ValidationRule $rule) use ($path, $rules) {
                if (! $rule instanceof Rule) {
                    $rules->add($rule);

                    return;
                }

                $rules->add(
                    ...$this->ruleAttributesResolver->execute($rule->getRules($path), $path)
                );
            });

        return $rules;
    }
}
