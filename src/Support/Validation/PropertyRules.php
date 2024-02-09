<?php

namespace Spatie\LaravelData\Support\Validation;

use Illuminate\Support\Arr;

class PropertyRules
{
    /**
     * @param array<int, ValidationRule> $rules
     */
    public function __construct(
        protected array $rules = []
    ) {
    }

    public static function create(ValidationRule ...$rules): self
    {
        return (new self())->add(...$rules);
    }

    public function add(ValidationRule ...$rules): static
    {
        $this->removeType(...$rules);

        array_push($this->rules, ...$rules);

        return $this;
    }

    public function prepend(ValidationRule ...$rules): static
    {
        $this->removeType(...$rules);

        $this->rules = Arr::prepend($this->rules, ...$rules);

        return $this;
    }

    public function removeType(string|ValidationRule ...$classes): static
    {
        foreach ($this->rules as $i => $rule) {
            foreach ($classes as $class) {
                if ($class instanceof RequiringRule && $rule instanceof RequiringRule) {
                    unset($this->rules[$i]);

                    continue 2;
                }

                if ($rule instanceof $class) {
                    unset($this->rules[$i]);

                    continue 2;
                }
            }
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

    public function all(): array
    {
        return $this->rules;
    }
}
