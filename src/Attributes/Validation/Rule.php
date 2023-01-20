<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Contracts\Validation\InvokableRule as InvokableRuleContract;
use Illuminate\Contracts\Validation\Rule as RuleContract;
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

    public function get(): array
    {
        return $this->rules;
    }
}
