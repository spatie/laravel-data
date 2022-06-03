<?php

namespace Spatie\LaravelData\Support\Validation;

use Illuminate\Support\Collection;

class RulesCollection
{
    /** @var Collection<\Spatie\LaravelData\Support\Validation\ValidationRule> */
    protected Collection $rules;

    public function __construct()
    {
        $this->rules = new Collection();
    }

    public static function create(): self
    {
        return new self();
    }

    public function add(ValidationRule ...$rules): static
    {
        $this->removeType(...$rules);
        $this->rules->push(...$rules);

        return $this;
    }

    public function removeType(string|ValidationRule ...$classes): static
    {
        $this->rules = $this->rules->reject(function (ValidationRule $rule) use ($classes) {
            foreach ($classes as $class) {
                if ($rule instanceof $class) {
                    return true;
                }
            }

            return false;
        })->values();

        return $this;
    }

    public function hasType(string $class): bool
    {
        return $this->rules->contains(fn (ValidationRule $rule) => $rule instanceof $class);
    }

    public function normalize(): array
    {
        return $this->rules
            ->map(fn (ValidationRule $rule) => $rule->getRules())
            ->flatten()
            ->all();
    }

    public function all(): array
    {
        return $this->rules->all();
    }
}
