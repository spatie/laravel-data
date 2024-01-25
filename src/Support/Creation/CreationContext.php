<?php

namespace Spatie\LaravelData\Support\Creation;

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
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\DataContainer;

/**
 * @template TData of BaseData
 */
class CreationContext
{
    /**
     * @param class-string<TData> $dataClass
     * @param array<string|int> $currentPath
     */
    public function __construct(
        public string $dataClass,
        public array $mappedProperties,
        public array $currentPath,
        public ValidationStrategy $validationStrategy,
        public readonly bool $mapPropertyNames,
        public readonly bool $disableMagicalCreation,
        public readonly ?array $ignoredMagicalMethods,
        public readonly ?GlobalCastsCollection $casts,
    ) {
    }

    /**
     * @return TData
     */
    public function from(mixed ...$payloads): BaseData
    {
        return DataContainer::get()->dataFromSomethingResolver()->execute(
            $this->dataClass,
            $this,
            ...$payloads
        );
    }

    /**
     * @template TCollectKey of array-key
     * @template TCollectValue
     *
     * @param Collection<TCollectKey, TCollectValue>|EloquentCollection<TCollectKey, TCollectValue>|LazyCollection<TCollectKey, TCollectValue>|Enumerable|array<TCollectKey, TCollectValue>|AbstractPaginator|PaginatorContract|AbstractCursorPaginator|CursorPaginatorContract|DataCollection<TCollectKey, TCollectValue> $items
     *
     * @return ($into is 'array' ? array<TCollectKey, TData> : ($into is class-string<EloquentCollection> ? Collection<TCollectKey, TData> : ($into is class-string<Collection> ? Collection<TCollectKey, TData> : ($into is class-string<LazyCollection> ? LazyCollection<TCollectKey, TData> : ($into is class-string<DataCollection> ? DataCollection<TCollectKey, TData> : ($into is class-string<PaginatedDataCollection> ? PaginatedDataCollection<TCollectKey, TData> : ($into is class-string<CursorPaginatedDataCollection> ? CursorPaginatedDataCollection<TCollectKey, TData> : ($items is EloquentCollection ? Collection<TCollectKey, TData> : ($items is Collection ? Collection<TCollectKey, TData> : ($items is LazyCollection ? LazyCollection<TCollectKey, TData> : ($items is Enumerable ? Enumerable<TCollectKey, TData> : ($items is array ? array<TCollectKey, TData> : ($items is AbstractPaginator ? AbstractPaginator : ($items is PaginatorContract ? PaginatorContract : ($items is AbstractCursorPaginator ? AbstractCursorPaginator : ($items is CursorPaginatorContract ? CursorPaginatorContract : ($items is DataCollection ? DataCollection<TCollectKey, TData> : ($items is CursorPaginator ? CursorPaginatedDataCollection<TCollectKey, TData> : ($items is Paginator ? PaginatedDataCollection<TCollectKey, TData> : DataCollection<TCollectKey, TData>)))))))))))))))))))
     */
    public function collect(
        mixed $items,
        ?string $into = null
    ): array|DataCollection|PaginatedDataCollection|CursorPaginatedDataCollection|Enumerable|AbstractPaginator|PaginatorContract|AbstractCursorPaginator|CursorPaginatorContract|LazyCollection|Collection {
        return DataContainer::get()->dataCollectableFromSomethingResolver()->execute(
            $this->dataClass,
            $this,
            $items,
            $into
        );
    }

    /** @internal */
    public function next(
        string $dataClass,
        string|int $path,
    ): self {
        $this->dataClass = $dataClass;

        array_push($this->currentPath, $path);

        return $this;
    }

    /** @internal */
    public function previous(): self
    {
        array_pop($this->currentPath);

        return $this;
    }
}
