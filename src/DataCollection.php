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

class DataCollection implements Responsable, Arrayable, Jsonable, JsonSerializable, IteratorAggregate, Countable, ArrayAccess, EloquentCastable
{
    use ResponsableData;
    use IncludeableData;

    private ?Closure $through = null;

    private ?Closure $filter = null;

    private Enumerable|CursorPaginator|Paginator $items;

    public function __construct(
        private string $dataClass,
        Enumerable|array|CursorPaginator|Paginator $items
    ) {
        $this->items = is_array($items) ? new Collection($items) : $items;

        $this->ensureAllItemsAreData();
    }

    public function through(Closure $through): static
    {
        $this->through = $through;

        return $this;
    }

    public function filter(Closure $filter): static
    {
        $this->filter = $filter;

        return $this;
    }

    public function items(): array|CursorPaginator|Paginator
    {
        return $this->isPaginated()
            ? $this->items
            : $this->items->all();
    }

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

    public function all(): array
    {
        return $this->transform(TransformationType::withoutValueTransforming());
    }

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

    public function toCollection(): Enumerable
    {
        if ($this->isPaginated()) {
            throw InvalidDataCollectionModification::cannotCastToCollection();
        }

        /** @var \Illuminate\Support\Enumerable $items */
        $items = $this->items;

        return $items;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->transform(TransformationType::withoutValueTransforming()));
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function offsetExists($offset): bool
    {
        return $this->items->offsetExists($offset);
    }

    public function offsetGet($offset): Data
    {
        return $this->items->offsetGet($offset);
    }

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
        $closure = fn ($item) => $item instanceof Data ? $item : $this->dataClass::from($item);

        $this->items = $this->isPaginated()
            ? $this->items->through($closure)
            : $this->items->map($closure);
    }
}
