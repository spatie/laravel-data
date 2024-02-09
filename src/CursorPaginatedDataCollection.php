<?php

namespace Spatie\LaravelData;

use Closure;
use Countable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Pagination\CursorPaginator;
use IteratorAggregate;
use Spatie\LaravelData\Concerns\BaseDataCollectable;
use Spatie\LaravelData\Concerns\ContextableData;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Concerns\TransformableData;
use Spatie\LaravelData\Concerns\WrappableData;
use Spatie\LaravelData\Contracts\BaseDataCollectable as BaseDataCollectableContract;
use Spatie\LaravelData\Contracts\IncludeableData as IncludeableDataContract;
use Spatie\LaravelData\Contracts\ResponsableData as ResponsableDataContract;
use Spatie\LaravelData\Contracts\TransformableData as TransformableDataContract;
use Spatie\LaravelData\Contracts\WrappableData as WrappableDataContract;
use Spatie\LaravelData\Exceptions\CannotCastData;
use Spatie\LaravelData\Exceptions\PaginatedCollectionIsAlwaysWrapped;
use Spatie\LaravelData\Support\EloquentCasts\DataCollectionEloquentCast;

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements  IteratorAggregate<TKey, TValue>
 */
class CursorPaginatedDataCollection implements Responsable, BaseDataCollectableContract, TransformableDataContract, ResponsableDataContract, IncludeableDataContract, WrappableDataContract, IteratorAggregate, Countable
{
    use ResponsableData;
    use IncludeableData;
    use WrappableData;
    use TransformableData;

    /** @use \Spatie\LaravelData\Concerns\BaseDataCollectable<TKey, TValue> */
    use BaseDataCollectable;
    use ContextableData;

    /** @var CursorPaginator<TValue> */
    protected CursorPaginator $items;

    /**
     * @param class-string<TValue> $dataClass
     * @param CursorPaginator<TValue> $items
     */
    public function __construct(
        public readonly string $dataClass,
        CursorPaginator $items
    ) {
        $this->items = $items->through(
            fn ($item) => $item instanceof $this->dataClass ? $item : $this->dataClass::from($item)
        );
    }

    /**
     * @template TOtherValue
     *
     * @param Closure(TValue, TKey): TOtherValue $through
     *
     * @return static<TKey, TOtherValue>
     */
    public function through(Closure $through): static
    {
        $clone = clone $this;

        $clone->items = $clone->items->through($through);

        return $clone;
    }

    /**
     * @return CursorPaginator<TValue>
     */
    public function items(): CursorPaginator
    {
        return $this->items;
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
