<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\Dimensions;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\ExcludeIf;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\Rules\NotIn;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\ProhibitedIf;
use Illuminate\Validation\Rules\RequiredIf;
use Illuminate\Validation\Rules\Unique;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Support\Validation\CustomValidationRule;
use Spatie\LaravelData\Support\Validation\RuleFactory;
use Spatie\LaravelData\Support\Validation\Rules\FoundationDimensions;
use Spatie\LaravelData\Support\Validation\Rules\FoundationEnum;
use Spatie\LaravelData\Support\Validation\Rules\FoundationExcludeIf;
use Spatie\LaravelData\Support\Validation\Rules\FoundationExists;
use Spatie\LaravelData\Support\Validation\Rules\FoundationIn;
use Spatie\LaravelData\Support\Validation\Rules\FoundationNotIn;
use Spatie\LaravelData\Support\Validation\Rules\FoundationPassword;
use Spatie\LaravelData\Support\Validation\Rules\FoundationProhibitedIf;
use Spatie\LaravelData\Support\Validation\Rules\FoundationRequiredIf;
use Spatie\LaravelData\Support\Validation\Rules\FoundationUnique;
use Spatie\LaravelData\Support\Validation\UnknownValidationRule;
use Spatie\LaravelData\Support\Validation\ValidationRule;

class RuleAttributesResolver
{
    public function __construct(private RuleFactory $ruleFactory)
    {
    }

    public function execute(array $rules): array
    {
        $rules = array_map(fn(mixed $rule) => match (true) {
            is_string($rule) => $this->resolveStringRule($rule),
            $rule instanceof ValidationRule => $rule,
            $rule instanceof Dimensions => new FoundationDimensions($rule),
            $rule instanceof Enum => new FoundationEnum($rule),
            $rule instanceof ExcludeIf => new FoundationExcludeIf($rule),
            $rule instanceof Exists => new FoundationExists($rule),
            $rule instanceof In => new FoundationIn($rule),
            $rule instanceof NotIn => new FoundationNotIn($rule),
            $rule instanceof Password => new FoundationPassword($rule),
            $rule instanceof ProhibitedIf => new FoundationProhibitedIf($rule),
            $rule instanceof RequiredIf => new FoundationRequiredIf($rule),
            $rule instanceof Unique => new FoundationUnique($rule),
            $rule instanceof RuleContract => new Rule($rule),
            default => new Rule($rule),
        }, $rules);

        return Arr::flatten($rules);
    }

    private function resolveStringRule(string $rule): array
    {
        $rules = explode('|', $rule);

        return $this->execute($rules);
    }
}
