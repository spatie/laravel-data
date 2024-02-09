<?php

namespace Spatie\LaravelData\Concerns;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;

/**
 * @property class-string<DataCollection> $_collectionClass
 * @property class-string<PaginatedDataCollection> $_paginatedCollectionClass
 * @property class-string<CursorPaginatedDataCollection> $_cursorPaginatedCollectionClass
 */
trait WithDeprecatedCollectionMethod
{
    /** @deprecated */
    public static function collection(Enumerable|array|AbstractPaginator|Paginator|AbstractCursorPaginator|CursorPaginator|DataCollection $items): DataCollection|CursorPaginatedDataCollection|PaginatedDataCollection
    {
        if ($items instanceof Paginator || $items instanceof AbstractPaginator) {
            return static::collect(
                $items,
                static::$_paginatedCollectionClass ?? PaginatedDataCollection::class
            );
        }

        if ($items instanceof AbstractCursorPaginator || $items instanceof CursorPaginator) {
            return static::collect(
                $items,
                static::$_cursorPaginatedCollectionClass ?? CursorPaginatedDataCollection::class
            );
        }

        return static::collect(
            $items,
            static::$_collectionClass ?? DataCollection::class
        );
    }
}
