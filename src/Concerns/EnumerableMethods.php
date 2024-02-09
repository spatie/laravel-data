<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\DataCollection;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements \ArrayAccess<TKey, TValue>
 */
trait EnumerableMethods
{
    /**
     * @template TMapValue
     *
     * @param callable(TValue, TKey): TMapValue $through
     *
     * @return static
     * @deprecated In v5, use a regular Laravel collection instead
     */
    public function through(callable $through): static
    {
        $cloned = clone $this;

        $cloned->items = $cloned->items->map(...func_get_args());

        return $cloned;
    }

    /**
     * @template TMapValue
     *
     * @param callable(TValue, TKey): TMapValue $map
     *
     * @return static
     * @deprecated In v5, use a regular Laravel collection instead
     *
     */
    public function map(callable $map): static
    {
        return $this->through(...func_get_args());
    }

    /**
     * @param callable(TValue): bool $filter
     *
     * @return static
     * @deprecated In v5, use a regular Laravel collection instead
     *
     */
    public function filter(callable $filter): static
    {
        $cloned = clone $this;

        $cloned->items = $cloned->items->filter(...func_get_args());

        return $cloned;
    }

    /**
     * @param callable(TValue): bool $filter
     *
     * @return static
     * @deprecated In v5, use a regular Laravel collection instead
     *
     */
    public function reject(callable $filter): static
    {
        $cloned = clone $this;

        $cloned->items = $cloned->items->reject(...func_get_args());

        return $cloned;
    }

    /**
     * @param null|         (callable(TValue,TKey): bool) $callback
     * @param TFirstDefault|(\Closure(): TFirstDefault) $default
     *
     * @return TValue|TFirstDefault
     * @deprecated In v5, use a regular Laravel collection instead
     *
     * @template            TFirstDefault
     *
     */
    public function first(callable|null $callback = null, $default = null)
    {
        return $this->items->first(...func_get_args());
    }

    /**
     * @param null|         (callable(TValue,TKey): bool) $callback
     * @param TLastDefault|(\Closure(): TLastDefault) $default
     *
     * @return TValue|TLastDefault
     * @deprecated In v5, use a regular Laravel collection instead
     *
     * @template            TLastDefault
     *
     */
    public function last(callable|null $callback = null, $default = null)
    {
        return $this->items->last(...func_get_args());
    }

    /**
     * @param callable(TValue, TKey): mixed $callback
     *
     * @return static
     * @deprecated In v5, use a regular Laravel collection instead
     *
     */
    public function each(callable $callback): static
    {
        $this->items->each(...func_get_args());

        return $this;
    }

    /**
     * @return static<int, TValue>
     * @deprecated In v5, use a regular Laravel collection instead
     *
     */
    public function values(): static
    {
        $cloned = clone $this;

        $cloned->items = $cloned->items->values();

        return $cloned;
    }

    /**
     * @deprecated In v5, use a regular Laravel collection instead
     */
    public function where(string $key, mixed $operator = null, mixed $value = null): static
    {
        $cloned = clone $this;

        $cloned->items = $cloned->items->where(...func_get_args());

        return $cloned;
    }

    /**
     * @param callable(TReduceInitial|TReduceReturnType, TValue, TKey): TReduceReturnType $callback
     * @param TReduceInitial $initial
     *
     * @return TReduceReturnType
     * @deprecated In v5, use a regular Laravel collection instead
     *
     * @template TReduceInitial
     * @template TReduceReturnType
     *
     */
    public function reduce(callable $callback, mixed $initial = null)
    {
        return $this->items->reduce(...func_get_args());
    }

    /**
     * @param (callable(TValue, TKey): bool)|string|null $key
     * @param mixed $operator
     * @param mixed $value
     *
     * @return TValue
     *
     * @throws \Illuminate\Support\ItemNotFoundException
     * @throws \Illuminate\Support\MultipleItemsFoundException
     * @deprecated In v5, use a regular Laravel collection instead
     *
     */
    public function sole(callable|string|null $key = null, mixed $operator = null, mixed $value = null)
    {
        return $this->items->sole(...func_get_args());
    }

    /**
     *
     *
     * @param DataCollection $collection
     *
     * @return static
     */
    public function merge(DataCollection $collection): static
    {
        $cloned = clone $this;

        $cloned->items = $cloned->items->merge($collection->items);

        return $cloned;
    }
}
