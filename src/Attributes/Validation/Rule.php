<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Contracts\Validation\Rule as RuleContract;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Rule extends ValidationAttribute
{
    protected array $rules = [];

    public function __construct(string | array | ValidationAttribute | RuleContract ...$rules)
    {
        foreach ($rules as $rule) {
            $newRules = match (true) {
                is_string($rule) => explode('|', $rule),
                $rule instanceof RuleContract => [$rule],
                is_array($rule) => $rule,
                $rule instanceof ValidationAttribute => $rule->getRules(),
            };

            $this->rules = array_merge($this->rules, $newRules);
        }
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
