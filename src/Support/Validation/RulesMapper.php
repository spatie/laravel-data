<?php

namespace Spatie\LaravelData\Support\Validation;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\Dimensions as DimensionsRule;
use Illuminate\Validation\Rules\Enum as EnumRule;
use Illuminate\Validation\Rules\ExcludeIf as ExcludeIfRule;
use Illuminate\Validation\Rules\Exists as ExistsRule;
use Illuminate\Validation\Rules\In as InRule;
use Illuminate\Validation\Rules\NotIn as NotInRule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\Rules\ProhibitedIf as ProhibitedIfRule;
use Illuminate\Validation\Rules\RequiredIf as RequiredIfRule;
use Illuminate\Validation\Rules\Unique as UniqueRule;
use Spatie\LaravelData\Attributes\Validation\Dimensions;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Exclude;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\NotIn;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Attributes\Validation\Prohibited;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Throwable;

class RulesMapper
{
    public function __construct(protected RuleFactory $ruleFactory)
    {
    }

    public function execute(array $rules): array
    {
        $rules = array_map(fn (mixed $rule) => match (true) {
            is_string($rule) => $this->resolveStringRule($rule),
            $rule instanceof ValidationRule => $rule,
            $rule instanceof DimensionsRule => new Dimensions(rule: $rule),
            $rule instanceof EnumRule => new Enum($rule),
            $rule instanceof ExcludeIfRule => new Exclude($rule),
            $rule instanceof ExistsRule => new Exists(rule: $rule),
            $rule instanceof InRule => new In($rule),
            $rule instanceof NotInRule => new NotIn($rule),
            $rule instanceof PasswordRule => new Password(rule: $rule),
            $rule instanceof ProhibitedIfRule => new Prohibited($rule),
            $rule instanceof RequiredIfRule => new Required($rule),
            $rule instanceof UniqueRule => new Unique(rule: $rule),
            $rule instanceof RuleContract => new Rule($rule),
            $rule instanceof Rule => $this->execute($rule->getRules()),
            default => new Rule($rule),
        }, $rules);

        return Arr::flatten($rules);
    }

    protected function resolveStringRule(string $rule): mixed
    {
        if (! str_contains($rule, '|')) {
            try {
                return $this->ruleFactory->create($rule);
            } catch (Throwable $t) {
                return new Rule($rule);
            }
        }

        $rules = explode('|', $rule);

        return $this->execute($rules);
    }
}
