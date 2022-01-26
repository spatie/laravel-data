<?php

namespace Spatie\LaravelData\RuleInferrers;

use Illuminate\Validation\Rules\RequiredIf;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Support\DataProperty;

class RequiredRuleInferrer implements RuleInferrer
{
    public function handle(DataProperty $property, array $rules): array
    {
        if ($this->shouldAddRule($property, $rules)) {
            $rules[] = 'required';
        }

        return $rules;
    }

    protected function shouldAddRule(DataProperty $property, array $rules): bool
    {
        if ($property->isNullable() || $property->isUndefinable()) {
            return false;
        }

        if ($property->isDataCollection() && in_array('present', $rules)) {
            return false;
        }

        foreach ($rules as $rule) {
            if (in_array($rule, ['boolean', 'nullable'])) {
                return false;
            }

            if (is_string($rule) && str_starts_with($rule, 'required')) {
                return false;
            }

            if ($rule instanceof RequiredIf || $rule instanceof Nullable) {
                return false;
            }
        }

        return true;
    }
}
