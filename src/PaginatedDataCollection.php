<?php

namespace Spatie\LaravelData;

use ArrayIterator;
use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\CursorPaginator;
use Spatie\LaravelData\Concerns\BaseDataCollectable;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Concerns\TransformableData;
use Spatie\LaravelData\Concerns\WrappableData;
use Spatie\LaravelData\Contracts\DataCollectable;
use Spatie\LaravelData\Exceptions\CannotCastData;
use Spatie\LaravelData\Exceptions\PaginatedCollectionIsAlwaysWrapped;
use Spatie\LaravelData\Support\EloquentCasts\DataCollectionEloquentCast;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Spatie\LaravelData\Transformers\DataCollectionTransformer;

/**
 * @template TValue
 *
 * @implements  DataCollectable<TValue>
 */
class PaginatedDataCollection implements DataCollectable
{
    use ResponsableData;
    use IncludeableData;
    use WrappableData;
    use TransformableData;
    use BaseDataCollectable;

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
            fn ($item) => $item instanceof $this->dataClass ? $item : $this->dataClass::from($item)
        );
    }

    /**
     * @param Closure(TValue, array-key): TValue $through
     *
     * @return static
     */
    public function through(Closure $through): static
    {
        $clone = clone $this;

        $clone->items = $clone->items->through($through);

        return $clone;
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
    ): array {
        $transformer = new DataCollectionTransformer(
            $this->dataClass,
            $transformValues,
            $wrapExecutionType,
            $this->getPartialTrees(),
            $this->items,
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
