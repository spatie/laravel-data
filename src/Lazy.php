<?php

namespace Spatie\LaravelData;

use Closure;
use Illuminate\Database\Eloquent\Model;

class Lazy
{
    private ?Closure $condition = null;

    private ?bool $defaultIncluded = null;

    protected function __construct(
        private Closure $value
    ) {
    }

    public static function create(Closure $value): self
    {
        return new self($value);
    }

    public static function when(Closure $condition, Closure $value): self
    {
        return self::create($value)->condition($condition);
    }

    public static function whenLoaded(string $relation, Model $model, Closure $value): static
    {
        return self::when(
            fn() => $model->relationLoaded($relation),
            fn() => $model->{$relation} !== null ? $value() : null,
        );
    }

    public function defaultIncluded(bool $defaultIncluded = true): self
    {
        $this->defaultIncluded = $defaultIncluded;

        return $this;
    }

    public function condition(Closure $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    public function isConditional(): bool
    {
        return $this->condition !== null;
    }

    public function getCondition(): ?Closure
    {
        return $this->condition;
    }

    public function isDefaultIncluded(): bool
    {
        return $this->defaultIncluded ?? false;
    }

    public function resolve(): mixed
    {
        return ($this->value)();
    }
}
