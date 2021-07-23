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

    public static function whenLoaded(string $relation, Model $model, Closure $value)
    {
        return self::when(
            fn () => $model->relationLoaded($relation),
            $value,
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

    public function shouldInclude(): bool
    {
        if ($this->defaultIncluded) {
            return true;
        }

        if ($this->condition === null) {
            return false;
        }

        return ($this->condition)();
    }

    public function resolve(): mixed
    {
        return ($this->value)();
    }
}
