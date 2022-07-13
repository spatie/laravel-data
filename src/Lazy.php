<?php

namespace Spatie\LaravelData;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Support\Lazy\ConditionalLazy;
use Spatie\LaravelData\Support\Lazy\DefaultLazy;
use Spatie\LaravelData\Support\Lazy\InertiaLazy;
use Spatie\LaravelData\Support\Lazy\RelationalLazy;

abstract class Lazy
{
    protected ?bool $defaultIncluded = null;

    public static function create(Closure $value): DefaultLazy
    {
        return new DefaultLazy($value);
    }

    public static function when(Closure $condition, Closure $value): ConditionalLazy
    {
        return new ConditionalLazy($condition, $value);
    }

    public static function whenLoaded(string $relation, Model $model, Closure $value): RelationalLazy
    {
        return new RelationalLazy($relation, $model, $value);
    }

    public static function inertia(Closure $value): InertiaLazy
    {
        return new InertiaLazy($value);
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
