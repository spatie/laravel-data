<?php

namespace Spatie\LaravelData\Contracts;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\DataPipeline;
use Spatie\LaravelData\PaginatedDataCollection;

/**
 * @template TValue
 */
interface BaseData
{
    public static function optional(mixed ...$payloads): ?static;

    public static function from(mixed ...$payloads): static;

    public static function withoutMagicalCreationFrom(mixed ...$payloads): static;

    public static function collect(mixed $items, ?string $into = null): array|DataCollection|PaginatedDataCollection|CursorPaginatedDataCollection|Enumerable|AbstractPaginator|Paginator|AbstractCursorPaginator|CursorPaginator;

    public static function withoutMagicalCreationCollect(mixed $items, ?string $into = null): array|DataCollection|PaginatedDataCollection|CursorPaginatedDataCollection|Enumerable|AbstractPaginator|Paginator|AbstractCursorPaginator|CursorPaginator;

    public static function normalizers(): array;

    public static function prepareForPipeline(\Illuminate\Support\Collection $properties): \Illuminate\Support\Collection;

    public static function pipeline(): DataPipeline;
    public static function empty(array $extra = []): array;

    public function getMorphClass(): string;
}
