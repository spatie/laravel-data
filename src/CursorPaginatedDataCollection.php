<?php

namespace Spatie\LaravelData;

use Closure;
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

/**
 * @template TKey of array-key
 * @template TValue
 *
 * @implements  DataCollectable<TKey, TValue>
 */
class CursorPaginatedDataCollection implements DataCollectable
{
    use ResponsableData;
    use IncludeableData;
    use WrappableData;
    use TransformableData;

    /** @use \Spatie\LaravelData\Concerns\BaseDataCollectable<TKey, TValue> */
    use BaseDataCollectable;

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
     * @param Closure(TValue, TKey): TValue $through
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
