<?php

namespace Spatie\LaravelData;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Support\Lazy\ConditionalLazy;
use Spatie\LaravelData\Support\Lazy\DefaultLazy;
use Spatie\LaravelData\Support\Lazy\RelationalLazy;

abstract class Lazy
{
    private ?bool $defaultIncluded = null;

    public static function create(Closure $value): static
    {
        return new DefaultLazy($value);
    }

    public static function when(Closure $condition, Closure $value): static
    {
        return new ConditionalLazy($condition, $value);
    }

    public static function whenLoaded(string $relation, Model $model, Closure $value): static
    {
        return new RelationalLazy($relation, $model, $value);
    }

    abstract public function resolve(): mixed;

    public function defaultIncluded(bool $defaultIncluded = true): self
    {
        $this->defaultIncluded = $defaultIncluded;

        return $this;
    }

    public function isDefaultIncluded(): bool
    {
        return $this->defaultIncluded ?? false;
    }
}
