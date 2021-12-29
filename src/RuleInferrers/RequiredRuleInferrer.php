<?php

namespace Spatie\LaravelData\RuleInferrers;

use Illuminate\Validation\Rules\RequiredIf;
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
        if ($property->isNullable()) {
            return false;
        }


        foreach ($rules as $rule) {
            if ($rule === 'boolean') {
                return false;
            }

            if (is_string($rule) && str_starts_with($rule, 'required')) {
                return false;
            }

            if ($rule instanceof RequiredIf) {
                return false;
            }
        }

        return true;
    }
}
