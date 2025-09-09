<?php

namespace Spatie\LaravelData;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Macroable;
use Spatie\LaravelData\Support\Lazy\ClosureLazy;
use Spatie\LaravelData\Support\Lazy\ConditionalLazy;
use Spatie\LaravelData\Support\Lazy\DefaultLazy;
use Spatie\LaravelData\Support\Lazy\InertiaDeferred;
use Spatie\LaravelData\Support\Lazy\InertiaLazy;
use Spatie\LaravelData\Support\Lazy\RelationalLazy;

abstract class Lazy
{
    use Macroable;

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

    public static function inertiaDeferred(mixed $value, ?string $group = null): InertiaDeferred
    {
        return new InertiaDeferred($value, $group);
    }

    public static function closure(Closure $closure): ClosureLazy
    {
        return new ClosureLazy($closure);
    }

    abstract public function resolve(): mixed;

    abstract public function __serialize(): array;

    abstract public function __unserialize(array $data): void;

    public function defaultIncluded(bool $defaultIncluded = true): self
    {
        $this->defaultIncluded = $defaultIncluded;

        return $this;
    }

    public function isDefaultIncluded(): bool
    {
        return $this->defaultIncluded ?? false;
    }

    public function __get(string $name): mixed
    {
        return $this->resolve()->$name;
    }

    public function __call(string $name, array $arguments): mixed
    {
        return call_user_func_array([$this->resolve(), $name], $arguments);
    }
}
