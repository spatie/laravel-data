<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Contracts\DataCollectable;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements \ArrayAccess<TKey, TValue>
 * @implements  DataCollectable<TValue>
 */
trait EnumerableMethods
{
    /**
     * @param callable(TValue, TKey): TValue $through
     *
     * @return static
     */
    public function through(callable $through): static
    {
        $cloned = clone $this;

        $cloned->items = $cloned->items->map($through);

        return $cloned;
    }

    /**
     * @param callable(TValue, TKey): TValue $map
     *
     * @return static
     */
    public function map(callable $map): static
    {
        return $this->through($map);
    }

    /**
     * @param callable(TValue): bool $filter
     *
     * @return static
     */
    public function filter(callable $filter): static
    {
        $cloned = clone $this;

        $cloned->items = $cloned->items->filter($filter);

        return $cloned;
    }

    /**
     * @template            TFirstDefault
     *
     * @param null|         (callable(TValue,TKey): bool) $callback
     * @param TFirstDefault|(\Closure(): TFirstDefault)  $default
     *
     * @return TValue|TFirstDefault
     */
    public function first(callable|null $callback = null, $default = null)
    {
        return $this->items->first($callback, $default);
    }

    /**
     * @param callable(TValue, TKey): mixed $callback
     *
     * @return static
     */
    public function each(callable $callback): static
    {
        $this->items->each($callback);

        return $this;
    }

    /**
     * @return static<int, TValue>
     */
    public function values(): static
    {
        $cloned = clone $this;

        $cloned->items = $cloned->items->values();

        return $cloned;
    }

    public function where(string $key, mixed $operator = null, mixed $value = null): static
    {
        $cloned = clone $this;

        $cloned->items = $cloned->items->where($key, $operator, $value);

        return $cloned;
    }

    /**
     * @template TReduceInitial
     * @template TReduceReturnType
     *
     * @param callable(TReduceInitial|TReduceReturnType, TValue, TKey): TReduceReturnType  $callback
     * @param TReduceInitial $initial
     *
     * @return TReduceReturnType
     */
    public function reduce(callable $callback, mixed $initial = null)
    {
        return $this->items->reduce($callback, $initial);
    }

    /**
     * @param  (callable(TValue, TKey): bool)|string|null  $key
     * @param mixed $operator
     * @param mixed $value
     *
     * @return TValue
     *
     * @throws \Illuminate\Support\ItemNotFoundException
     * @throws \Illuminate\Support\MultipleItemsFoundException
     */
    public function sole(callable|string|null $key = null, mixed $operator = null, mixed $value = null)
    {
        return $this->items->sole(...func_get_args());
    }
}
