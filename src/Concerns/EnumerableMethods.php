<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\DataCollection;

/**
 * @template TKey of array-key
 * @template TValue
 * @template TMapValue
 *
 * @implements \ArrayAccess<TKey, TValue>
 */
trait EnumerableMethods
{
    /**
     * @deprecated In v5, use a regular Laravel collection instead
     *
     * @param callable(TValue, TKey): TMapValue $through
     *
     * @return static
     */
    public function through(callable $through): static
    {
        $cloned = clone $this;

        $cloned->items = $cloned->items->map(...func_get_args());

        return $cloned;
    }

    /**
     * @deprecated In v5, use a regular Laravel collection instead
     *
     * @param callable(TValue, TKey): TMapValue $map
     *
     * @return static
     */
    public function map(callable $map): static
    {
        return $this->through(...func_get_args());
    }

    /**
     * @deprecated In v5, use a regular Laravel collection instead
     *
     * @param callable(TValue): bool $filter
     *
     * @return static
     */
    public function filter(callable $filter): static
    {
        $cloned = clone $this;

        $cloned->items = $cloned->items->filter(...func_get_args());

        return $cloned;
    }

    /**
     * @deprecated In v5, use a regular Laravel collection instead
     *
     * @param callable(TValue): bool $filter
     *
     * @return static
     */
    public function reject(callable $filter): static
    {
        $cloned = clone $this;

        $cloned->items = $cloned->items->reject(...func_get_args());

        return $cloned;
    }

    /**
     * @deprecated In v5, use a regular Laravel collection instead
     *
     * @template            TFirstDefault
     *
     * @param null|         (callable(TValue,TKey): bool) $callback
     * @param TFirstDefault|(\Closure(): TFirstDefault)  $default
     *
     * @return TValue|TFirstDefault
     */
    public function first(callable|null $callback = null, $default = null)
    {
        return $this->items->first(...func_get_args());
    }

    /**
     * @deprecated In v5, use a regular Laravel collection instead
     *
     * @template            TLastDefault
     *
     * @param null|         (callable(TValue,TKey): bool) $callback
     * @param TLastDefault|(\Closure(): TLastDefault)  $default
     *
     * @return TValue|TLastDefault
     */
    public function last(callable|null $callback = null, $default = null)
    {
        return $this->items->last(...func_get_args());
    }

    /**
     * @deprecated In v5, use a regular Laravel collection instead
     *
     * @param callable(TValue, TKey): mixed $callback
     *
     * @return static
     */
    public function each(callable $callback): static
    {
        $this->items->each(...func_get_args());

        return $this;
    }

    /**
     * @deprecated In v5, use a regular Laravel collection instead
     *
     * @return static<int, TValue>
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
     * @deprecated In v5, use a regular Laravel collection instead
     *
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
        return $this->items->reduce(...func_get_args());
    }

    /**
     * @deprecated In v5, use a regular Laravel collection instead
     *
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
