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
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use IteratorAggregate;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Exceptions\CannotCastData;
use Spatie\LaravelData\Exceptions\InvalidPaginatedDataCollectionModification;
use Spatie\LaravelData\Support\EloquentCasts\DataCollectionEloquentCast;
use Spatie\LaravelData\Support\TransformationType;
use Spatie\LaravelData\Transformers\DataCollectionTransformer;

class DataCollection implements Responsable, Arrayable, Jsonable, IteratorAggregate, Countable, ArrayAccess, EloquentCastable
{
    use ResponsableData;
    use IncludeableData;

    private ?Closure $through = null;

    private ?Closure $filter = null;

    private array | AbstractPaginator | AbstractCursorPaginator | Paginator $items;

    public function __construct(
        private string $dataClass,
        Collection | array | AbstractPaginator | AbstractCursorPaginator | Paginator $items
    ) {
        $this->items = $items instanceof Collection ? $items->all() : $items;

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

    public function items(): array | AbstractPaginator | AbstractCursorPaginator | Paginator
    {
        return $this->items;
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

    public function toCollection(): Collection
    {
        return new Collection($this->items);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->transform(TransformationType::withoutValueTransforming()));
    }

    public function count()
    {
        return count($this->items);
    }

    public function offsetExists($offset): bool
    {
        return match (true) {
            is_array($this->items) => array_key_exists($offset, $this->items),
            $this->isPaginated() => array_key_exists($offset, $this->items->items())
        };
    }

    public function offsetGet($offset): Data
    {
        $item = match (true) {
            is_array($this->items) => $this->items[$offset],
            $this->isPaginated() => $this->items->items()[$offset]
        };

        return $item;
    }

    public function offsetSet($offset, $value): void
    {
        if ($this->isPaginated()) {
            throw InvalidPaginatedDataCollectionModification::cannotSetItem();
        }

        $value = $value instanceof Data
            ? $value
            : $this->dataClass::from($value);

        if (empty($offset)) {
            $this->items[] = $value;

            return;
        }

        $this->items[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        if ($this->isPaginated()) {
            throw InvalidPaginatedDataCollectionModification::cannotUnSetItem();
        }

        unset($this->items[$offset]);
    }

    public function isPaginated(): bool
    {
        return $this->items instanceof AbstractPaginator || $this->items instanceof AbstractCursorPaginator;
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
            : array_map($closure, $this->items);
    }
}
