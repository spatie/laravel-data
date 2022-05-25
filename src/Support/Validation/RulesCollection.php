<?php

namespace Spatie\LaravelData\Support\Validation;

use Spatie\LaravelData\Attributes\Validation\ValidationAttribute;

class RulesCollection
{
    protected array $rules = [];

    public static function create(): static
    {
        return new static();
    }

    public function add(ValidationRule $rule): static
    {
        $this->rules = array_filter(
            $this->rules,
            fn(ValidationRule $initialRule) => ! $initialRule instanceof $rule
        );

        $this->rules[] = $rule;

        return $this;
    }

    public function hasType(string $class): bool
    {
        foreach ($this->rules as $rule) {
            if ($rule instanceof $class) {
                return true;
            }
        }

        return false;
    }

    public function all(): array
    {
        return $this->rules;
    }
}
