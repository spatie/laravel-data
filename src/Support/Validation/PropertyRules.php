<?php

namespace Spatie\LaravelData\Support\Validation;

use Illuminate\Support\Collection;

class PropertyRules
{
    /** @var Collection<\Spatie\LaravelData\Support\Validation\ValidationRule> */
    protected Collection $rules;

    public function __construct()
    {
        $this->rules = new Collection();
    }

    public static function create(ValidationRule ...$rules): self
    {
        return (new self())->add(...$rules);
    }

    public function add(ValidationRule ...$rules): static
    {
        $this->removeType(...$rules);
        $this->rules->push(...$rules);

        return $this;
    }

    public function prepend(ValidationRule ...$rules): static
    {
        $this->removeType(...$rules);
        $this->rules->prepend(...$rules);

        return $this;
    }

    public function removeType(string|ValidationRule ...$classes): static
    {
        $this->rules = $this->rules->reject(function (ValidationRule $rule) use ($classes) {
            foreach ($classes as $class) {
                if ($class instanceof RequiringRule && $rule instanceof RequiringRule) {
                    return true;
                }

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

    public function all(): array
    {
        return $this->rules->all();
    }
}
