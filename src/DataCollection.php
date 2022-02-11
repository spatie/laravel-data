<?php

namespace Spatie\LaravelData;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use Illuminate\Contracts\Database\Eloquent\Castable as EloquentCastable;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use IteratorAggregate;
use JsonSerializable;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Exceptions\CannotCastData;
use Spatie\LaravelData\Exceptions\InvalidDataCollectionModification;
use Spatie\LaravelData\Support\EloquentCasts\DataCollectionEloquentCast;
use Spatie\LaravelData\Support\TransformationType;
use Spatie\LaravelData\Transformers\DataCollectionTransformer;

/**
 * @template TValue
 *
 * @implements \ArrayAccess<array-key, TValue>
 * @implements  \Illuminate\Contracts\Support\Arrayable<array-key, TValue>
 * @implements  \IteratorAggregate<array-key, TValue>
 */
class DataCollection implements Responsable, Arrayable, Jsonable, JsonSerializable, IteratorAggregate, Countable, ArrayAccess, EloquentCastable
{
    use ResponsableData;
    use IncludeableData;

    private ?Closure $through = null;

    private ?Closure $filter = null;

    /** @var Enumerable<array-key, TValue>|CursorPaginator<TValue>|Paginator */
    private Enumerable|CursorPaginator|Paginator $items;

    /**
     * @param class-string<\Spatie\LaravelData\Data> $dataClass
     * @param array|Enumerable<array-key, TValue>|CursorPaginator<TValue>|Paginator $items
     */
    public function __construct(
        private string $dataClass,
        Enumerable|array|CursorPaginator|Paginator|DataCollection $items
    ) {
        $this->items = match (true) {
            is_array($items) => new Collection($items),
            $items instanceof DataCollection => $items->toCollection(),
            default => $items
        };

        $this->ensureAllItemsAreData();
    }

    /**
     * @param Closure(TValue, array-key): TValue $through
     *
     * @return static
     */
    public function through(Closure $through): static
    {
        $this->through = $through;

        return $this;
    }

    /**
     * @param Closure(TValue, array-key): bool $filter
     *
     * @return static
     */
    public function filter(Closure $filter): static
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return array<array-key, TValue>|CursorPaginator<TValue>|Paginator
     */
    public function items(): array|CursorPaginator|Paginator
    {
        return $this->isPaginated()
            ? $this->items
            : $this->items->all();
    }

    /**
     * @param \Spatie\LaravelData\Support\TransformationType $type
     *
     * @return array<array>
     */
    public function transform(TransformationType $type): array
    {
        $transformer = new DataCollectionTransformer(
            $this->dataClass,
            $type,
            $this->getInclusionTree(),
            $this->getExclusionTree(),
            $this->items,
            $this->through,
            $this->filter
        );

        return $transformer->transform();
    }

    /**
     * @return array<array>
     */
    public function all(): array
    {
        return $this->transform(TransformationType::withoutValueTransforming());
    }

    /**
     * @return array<array>
     */
    public function toArray(): array
    {
        return $this->transform(TransformationType::full());
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /** @return \Illuminate\Support\Enumerable<array-key, TValue> */
    public function toCollection(): Enumerable
    {
        if ($this->isPaginated()) {
            throw InvalidDataCollectionModification::cannotCastToCollection();
        }

        /** @var \Illuminate\Support\Enumerable $items */
        $items = $this->items;

        return $items;
    }

    /**  @return \ArrayIterator<array-key, array> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->transform(TransformationType::withoutValueTransforming()));
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @param array-key $offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->items->offsetExists($offset);
    }

    /**
     * @param array-key $offset
     *
     * @return TValue
     */
    public function offsetGet($offset): mixed
    {
        return $this->items->offsetGet($offset);
    }

    /**
     * @param array-key|null $offset
     * @param TValue $value
     *
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if ($this->isPaginated() || $this->items instanceof LazyCollection) {
            throw InvalidDataCollectionModification::cannotSetItem();
        }

        $value = $value instanceof Data
            ? $value
            : $this->dataClass::from($value);

        $this->items->offsetSet($offset, $value);
    }

    /**
     * @param array-key $offset
     *
     * @return void
     */
    public function offsetUnset($offset): void
    {
        if ($this->isPaginated() || $this->items instanceof LazyCollection) {
            throw InvalidDataCollectionModification::cannotUnSetItem();
        }

        $this->items->offsetUnset($offset);
    }

    public function isPaginated(): bool
    {
        return $this->items instanceof CursorPaginator || $this->items instanceof Paginator;
    }

    public static function castUsing(array $arguments)
    {
        if (count($arguments) !== 1) {
            throw CannotCastData::dataCollectionTypeRequired();
        }

        return new DataCollectionEloquentCast(current($arguments));
    }

    protected function ensureAllItemsAreData()
    {
        $closure = fn ($item) => $item instanceof $this->dataClass ? $item : $this->dataClass::from($item);

        $this->items = $this->isPaginated()
            ? $this->items->through($closure)
            : $this->items->map($closure);
    }
}
