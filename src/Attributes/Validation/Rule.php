<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Contracts\Validation\InvokableRule as InvokableRuleContract;
use Illuminate\Contracts\Validation\Rule as RuleContract;
use Spatie\LaravelData\Support\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Rule extends ValidationRule
{
    protected array $rules = [];

    public function __construct(string | array | ValidationRule | RuleContract | InvokableRuleContract ...$rules)
    {
        foreach ($rules as $rule) {
            $newRules = match (true) {
                is_string($rule) => explode('|', $rule),
                $rule instanceof RuleContract,
                $rule instanceof InvokableRuleContract => [$rule],
                is_array($rule) => $rule,
                $rule instanceof ValidationRule => $rule->getRules(),
            };

            $this->rules = array_merge($this->rules, $newRules);
        }
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
