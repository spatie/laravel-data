<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Validation implements ValidationAttribute
{
    private array $rules;

    public function __construct(string ...$rules)
    {
        $rules = $this->expandSeparators($rules);

        $this->rules = $rules;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    private function expandSeparators(array $rules): array
    {
        $expandedRules = [];

        foreach ($rules as $rule) {
            $rule = explode('|', $rule);

            $expandedRules = array_merge_recursive($expandedRules, $rule);
        }

        return $expandedRules;
    }
}
