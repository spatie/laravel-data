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

trait DeprecatedData
{
    /** @deprecated */
    public static function collection(Enumerable|array|AbstractPaginator|Paginator|AbstractCursorPaginator|CursorPaginator|DataCollection $items): DataCollection|CursorPaginatedDataCollection|PaginatedDataCollection
    {
        if ($items instanceof Paginator || $items instanceof AbstractPaginator) {
            return static::collect(
                $items,
                property_exists(static::class, '_paginatedCollectionClass') ? static::$_paginatedCollectionClass : PaginatedDataCollection::class
            );
        }

        if ($items instanceof AbstractCursorPaginator || $items instanceof CursorPaginator) {
            return static::collect(
                $items,
                property_exists(static::class, '_cursorPaginatedCollectionClass') ? static::$_cursorPaginatedCollectionClass : CursorPaginatedDataCollection::class
            );
        }

        return static::collect(
            $items,
            property_exists(static::class, '_collectionClass') ? static::$_collectionClass : DataCollection::class
        );
    }
}
