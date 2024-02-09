<?php

namespace Spatie\LaravelData\Contracts;

use Illuminate\Contracts\Pagination\CursorPaginator as CursorPaginatorContract;
use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\DataPipeline;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;

/**
 * @template TData
 * @template TValue of mixed
 * @template TKey of array-key
 */
interface BaseData
{
    /**
     * @return static|null
     */
    public static function optional(mixed ...$payloads): ?static;

    /**
     * @return static
     */
    public static function from(mixed ...$payloads): static;

    /**
     * @param Collection<TKey, TValue>|EloquentCollection<TKey, TValue>|LazyCollection<TKey, TValue>|Enumerable|array<TKey, TValue>|AbstractPaginator|PaginatorContract|AbstractCursorPaginator|CursorPaginatorContract|DataCollection<TKey, TValue> $items
     *
     * @return ($into is 'array' ? array<TKey, static> : ($into is class-string<EloquentCollection> ? Collection<TKey, static> : ($into is class-string<Collection> ? Collection<TKey, static> : ($into is class-string<LazyCollection> ? LazyCollection<TKey, static> : ($into is class-string<DataCollection> ? DataCollection<TKey, static> : ($into is class-string<PaginatedDataCollection> ? PaginatedDataCollection<TKey, static> : ($into is class-string<CursorPaginatedDataCollection> ? CursorPaginatedDataCollection<TKey, static> : ($items is EloquentCollection ? Collection<TKey, static> : ($items is Collection ? Collection<TKey, static> : ($items is LazyCollection ? LazyCollection<TKey, static> : ($items is Enumerable ? Enumerable<TKey, static> : ($items is array ? array<TKey, static> : ($items is AbstractPaginator ? AbstractPaginator : ($items is PaginatorContract ? PaginatorContract : ($items is AbstractCursorPaginator ? AbstractCursorPaginator : ($items is CursorPaginatorContract ? CursorPaginatorContract : ($items is DataCollection ? DataCollection<TKey, static> : ($items is CursorPaginator ? CursorPaginatedDataCollection<TKey, static> : ($items is Paginator ? PaginatedDataCollection<TKey, static> : DataCollection<TKey, static>)))))))))))))))))))
     */
    public static function collect(mixed $items, ?string $into = null): array|DataCollection|PaginatedDataCollection|CursorPaginatedDataCollection|Enumerable|AbstractPaginator|PaginatorContract|AbstractCursorPaginator|CursorPaginatorContract|LazyCollection|Collection;

    /**
     * @return CreationContextFactory<static>
     */
    public static function factory(?CreationContext $creationContext = null): CreationContextFactory;

    public static function normalizers(): array;

    public static function prepareForPipeline(array $properties): array;

    public static function pipeline(): DataPipeline;
}
