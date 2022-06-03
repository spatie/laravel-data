<?php

namespace Spatie\LaravelData\Support\Validation;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Attributes\Validation\ValidationAttribute;

class RulesCollection
{
    /** @var \Spatie\LaravelData\Support\Validation\ValidationRule[] */
    protected array $rules = [];

    public static function create(): static
    {
        return new static();
    }

    public function add(ValidationRule ...$rules): static
    {
        foreach ($rules as $rule) {
            $this->rules = array_filter(
                $this->rules,
                fn(ValidationRule $initialRule) => ! $initialRule instanceof $rule
            );

            $this->rules[] = $rule;
        }

        $this->rules = array_values($this->rules);

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

    public function normalize(): array
    {
        return Arr::flatten(array_map(
            fn(ValidationRule $rule) => $rule->getRules(),
            $this->rules
        ));
    }

    public function all(): array
    {
        return $this->rules;
    }
}
