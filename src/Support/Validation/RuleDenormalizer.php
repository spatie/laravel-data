<?php

namespace Spatie\LaravelData\Support\Validation;

use BackedEnum;
use DateTimeInterface;
use Illuminate\Contracts\Validation\InvokableRule as InvokableRuleContract;
use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\Validation\CustomValidationAttribute;
use Spatie\LaravelData\Attributes\Validation\ObjectValidationAttribute;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\StringValidationAttribute;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;
use Spatie\LaravelData\Support\Validation\References\FieldReference;

class RuleDenormalizer
{
    /** @return array<string|object|RuleContract|InvokableRuleContract> */
    public function execute(mixed $rule, ValidationPath $path): array
    {
        if (is_string($rule)) {
            return Str::contains($rule, 'regex:') ? [$rule] : explode('|', $rule);
        }

        if (is_array($rule)) {
            return Arr::flatten(array_map(
                fn (mixed $rule) => $this->execute($rule, $path),
                $rule
            ));
        }

        if ($rule instanceof StringValidationAttribute) {
            return $this->normalizeStringValidationAttribute($rule, $path);
        }

        if ($rule instanceof ObjectValidationAttribute) {
            return [$rule->getRule($path)];
        }

        if ($rule instanceof CustomValidationAttribute) {
            return Arr::wrap($rule->getRules($path));
        }

        if ($rule instanceof Rule) {
            return $this->execute($rule->get(), $path);
        }

        if ($rule instanceof RuleContract || $rule instanceof InvokableRuleContract) {
            return [$rule];
        }

        return [$rule];
    }

    protected function normalizeStringValidationAttribute(
        StringValidationAttribute $rule,
        ValidationPath $path
    ): array {
        $parameters = collect($rule->parameters())
            ->map(fn (mixed $value) => $this->normalizeRuleParameter($value, $path))
            ->reject(fn (mixed $value) => $value === null);

        if ($parameters->isEmpty()) {
            return [$rule->keyword()];
        }

        $parameters = $parameters->map(
            fn (mixed $value, int|string $key) => is_string($key) ? "{$key}={$value}" : $value
        );

        return ["{$rule->keyword()}:{$parameters->join(',')}"];
    }

    protected function normalizeRuleParameter(
        mixed $parameter,
        ValidationPath $path
    ): ?string {
        if ($parameter === null) {
            return null;
        }

        if (is_string($parameter) || is_numeric($parameter)) {
            return (string) $parameter;
        }

        if (is_bool($parameter)) {
            return $parameter ? 'true' : 'false';
        }

        if (is_array($parameter) && count($parameter) === 0) {
            return null;
        }

        if (is_array($parameter)) {
            $subParameters = array_map(
                fn (mixed $subParameter) => $this->normalizeRuleParameter($subParameter, $path),
                $parameter
            );

            return implode(',', $subParameters);
        }

        if ($parameter instanceof DateTimeInterface) {
            return $parameter->format(DATE_ATOM);
        }

        if ($parameter instanceof BackedEnum) {
            return $parameter->value;
        }

        if ($parameter instanceof FieldReference) {
            return $parameter->getValue($path);
        }

        if ($parameter instanceof ExternalReference) {
            return $this->normalizeRuleParameter($parameter->getValue(), $path);
        }

        return (string) $parameter;
    }
}
