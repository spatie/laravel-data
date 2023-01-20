<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Contracts\Validation\InvokableRule as InvokableRuleContract;
use Illuminate\Contracts\Validation\Rule as RuleContract;
use Spatie\LaravelData\Support\Validation\ValidationPath;
use Spatie\LaravelData\Support\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Rule extends ValidationRule
{
    /** @var array<string|array|ValidationRule|RuleContract|InvokableRuleContract> */
    protected array $rules = [];

    public function __construct(string|array|ValidationRule|RuleContract|InvokableRuleContract ...$rules)
    {
        $this->rules = $rules;
    }

    public function getRules(ValidationPath $path): array
    {
        $rules = [];

        foreach ($this->rules as $rule) {
            $newRules = match (true) {
                is_string($rule) => explode('|', $rule),
                $rule instanceof RuleContract, $rule instanceof InvokableRuleContract => [$rule],
                is_array($rule) => $rule,
                $rule instanceof ValidationRule => $rule->getRules($path),
            };

            if (empty($newRules)) {
                continue;
            }

            array_push($rules, ...$newRules);
        }

        return $rules;
    }
}
