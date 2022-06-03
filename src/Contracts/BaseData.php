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

interface BaseData
{
    public static function optional(mixed ...$payloads): ?BaseData;

    public static function from(mixed ...$payloads): BaseData;

    public static function collection(Enumerable|array|AbstractPaginator|AbstractCursorPaginator|Paginator|DataCollection $items): DataCollection|PaginatedDataCollection;

    public static function normalizers(): array;

    public static function pipeline(): DataPipeline;

    public static function empty(array $extra = []): array;
}
