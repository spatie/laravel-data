<?php

namespace Spatie\LaravelData\Contracts;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\DataPipeline;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;

/**
 * @template TValue of object
 */
interface BaseData
{
    /**
     * @return TValue|null
     */
    public static function optional(mixed ...$payloads): ?object;

    /**
     * @return TValue
     */
    public static function from(mixed ...$payloads): object;

    /**
     * @param \Illuminate\Support\Enumerable|array|\Illuminate\Pagination\AbstractPaginator|\Illuminate\Pagination\AbstractCursorPaginator|\Illuminate\Contracts\Pagination\Paginator|\Spatie\LaravelData\DataCollection $items
     *
     * @return \Spatie\LaravelData\DataCollection<TValue>|\Spatie\LaravelData\PaginatedDataCollection
     */
    public static function collection(Enumerable|array|AbstractPaginator|AbstractCursorPaginator|Paginator|DataCollection $items): DataCollection|PaginatedDataCollection;

    public static function normalizers(): array;

    public static function pipeline(): DataPipeline;

    public static function empty(array $extra = []): array;
}
