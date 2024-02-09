<?php

namespace Spatie\LaravelData\Contracts;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;

/**
 * @template TValue
 */
interface DeprecatedData
{
    /**
     * @param \Illuminate\Support\Enumerable<array-key, TValue>|TValue[]|\Illuminate\Pagination\AbstractPaginator|\Illuminate\Contracts\Pagination\Paginator|\Illuminate\Pagination\AbstractCursorPaginator|\Illuminate\Contracts\Pagination\CursorPaginator|\Spatie\LaravelData\DataCollection<array-key, TValue> $items
     *
     * @return ($items is \Illuminate\Pagination\AbstractCursorPaginator|\Illuminate\Pagination\CursorPaginator ? \Spatie\LaravelData\CursorPaginatedDataCollection<array-key, static> : ($items is \Illuminate\Pagination\Paginator|\Illuminate\Pagination\AbstractPaginator ? \Spatie\LaravelData\PaginatedDataCollection<array-key, static> : \Spatie\LaravelData\DataCollection<array-key, static>))
     */
    public static function collection(Enumerable|array|AbstractPaginator|Paginator|AbstractCursorPaginator|CursorPaginator|DataCollection $items): DataCollection|CursorPaginatedDataCollection|PaginatedDataCollection;
}
