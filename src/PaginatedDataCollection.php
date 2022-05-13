<?php

namespace Spatie\LaravelData;

use ArrayIterator;
use Closure;
use Countable;
use Illuminate\Contracts\Database\Eloquent\Castable as EloquentCastable;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Pagination\CursorPaginator;
use IteratorAggregate;
use JsonSerializable;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Concerns\TransformableData;
use Spatie\LaravelData\Concerns\WrapableData;
use Spatie\LaravelData\Exceptions\CannotCastData;
use Spatie\LaravelData\Exceptions\PaginatedCollectionIsAlwaysWrapped;
use Spatie\LaravelData\Support\EloquentCasts\DataCollectionEloquentCast;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Spatie\LaravelData\Transformers\DataCollectionTransformer;

/**
 * @template TValue
 *
 * @implements  \Illuminate\Contracts\Support\Arrayable<array-key, TValue>
 * @implements  \IteratorAggregate<array-key, TValue>
 */
class PaginatedDataCollection implements Responsable, Arrayable, Jsonable, JsonSerializable, IteratorAggregate, Countable, EloquentCastable
{
    use ResponsableData;
    use IncludeableData;
    use WrapableData;
    use TransformableData;

    private ?Closure $through = null;

    private ?Closure $filter = null;

    /** @var CursorPaginator<TValue>|Paginator */
    private CursorPaginator|Paginator $items;

    /**
     * @param class-string<TValue> $dataClass
     * @param CursorPaginator<TValue>|Paginator $items
     */
    public function __construct(
        public readonly string $dataClass,
        CursorPaginator|Paginator $items
    ) {
        $this->items = $items->through(
            fn($item) => $item instanceof $this->dataClass ? $item : $this->dataClass::from($item)
        );
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
     * @return CursorPaginator<TValue>|Paginator
     */
    public function items(): CursorPaginator|Paginator
    {
        return $this->items;
    }

    /**
     * @param bool $transformValues
     *
     * @return array<array>
     */
    public function transform(
        bool $transformValues = true,
        WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
    ): array
    {
        $transformer = new DataCollectionTransformer(
            $this->dataClass,
            $transformValues,
            $wrapExecutionType,
            $this->getPartialTrees(),
            $this->items,
            $this->through,
            $this->filter,
            $this->getWrap(),
        );

        return $transformer->transform();
    }

    /**  @return \ArrayIterator<array-key, array> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->transform(
            transformValues: false,
        ));
    }

    public function count(): int
    {
        return count($this->items);
    }

    public static function castUsing(array $arguments)
    {
        if (count($arguments) !== 1) {
            throw CannotCastData::dataCollectionTypeRequired();
        }

        return new DataCollectionEloquentCast(current($arguments));
    }

    public function withoutWrapping(): static
    {
        throw PaginatedCollectionIsAlwaysWrapped::create();
    }
}
