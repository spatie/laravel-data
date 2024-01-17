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
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\Casting\GlobalCastsCollection;
use Spatie\LaravelData\Support\DataContainer;

/**
 * @template TData of BaseData
 */
class CreationContextFactory
{
    /**
     * @param class-string<TData> $dataClass
     */
    public function __construct(
        public string $dataClass,
        public ValidationType $validationType,
        public bool $mapPropertyNames,
        public bool $withoutMagicalCreation,
        public ?array $ignoredMagicalMethods,
        public ?GlobalCastsCollection $casts,
    ) {
    }

    public static function createFromConfig(
        string $dataClass,
        ?array $config = null
    ): self {
        $config ??= config('data');

        return new self(
            dataClass: $dataClass,
            validationType: ValidationType::from($config['validation_type']),
            mapPropertyNames: true,
            withoutMagicalCreation: false,
            ignoredMagicalMethods: null,
            casts: null,
        );
    }

    public static function createFromContext(
        CreationContext $context
    ) {
        return new self(
            dataClass: $context->dataClass,
            validationType: $context->validationType,
            mapPropertyNames: $context->mapPropertyNames,
            withoutMagicalCreation: $context->withoutMagicalCreation,
            ignoredMagicalMethods: $context->ignoredMagicalMethods,
            casts: $context->casts,
        );
    }

    public function validationType(ValidationType $validationType): self
    {
        $this->validationType = $validationType;

        return $this;
    }

    public function disableValidation(): self
    {
        $this->validationType = ValidationType::Disabled;

        return $this;
    }

    public function onlyValidateRequests(): self
    {
        $this->validationType = ValidationType::OnlyRequests;

        return $this;
    }

    public function alwaysValidate(): self
    {
        $this->validationType = ValidationType::Always;

        return $this;
    }

    public function withoutPropertyNameMapping(bool $withoutPropertyNameMapping = true): self
    {
        $this->mapPropertyNames = ! $withoutPropertyNameMapping;

        return $this;
    }

    public function withoutMagicalCreation(bool $withoutMagicalCreation = true): self
    {
        $this->withoutMagicalCreation = $withoutMagicalCreation;

        return $this;
    }

    public function ignoreMagicalMethod(string ...$methods): self
    {
        $this->ignoredMagicalMethods ??= [];

        array_push($this->ignoredMagicalMethods, ...$methods);

        return $this;
    }

    /**
     * @param string $castable
     * @param Cast|class-string<Cast> $cast
     */
    public function withCast(
        string $castable,
        Cast | string $cast,
    ): self {
        $cast = is_string($cast) ? app($cast) : $cast;

        if ($this->casts === null) {
            $this->casts = new GlobalCastsCollection();
        }

        $this->casts->add($castable, $cast);

        return $this;
    }

    public function withCastCollection(
        GlobalCastsCollection $casts,
    ): self {
        if ($this->casts === null) {
            $this->casts = $casts;

            return $this;
        }

        $this->casts->merge($casts);

        return $this;
    }

    public function get(): CreationContext
    {
        return new CreationContext(
            dataClass: $this->dataClass,
            validationType: $this->validationType,
            mapPropertyNames: $this->mapPropertyNames,
            withoutMagicalCreation: $this->withoutMagicalCreation,
            ignoredMagicalMethods: $this->ignoredMagicalMethods,
            casts: $this->casts,
        );
    }

    /**
     * @return TData
     */
    public function from(mixed ...$payloads): BaseData
    {
        return DataContainer::get()->dataFromSomethingResolver()->execute(
            $this->dataClass,
            $this->get(),
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
    ): array | DataCollection | PaginatedDataCollection | CursorPaginatedDataCollection | Enumerable | AbstractPaginator | PaginatorContract | AbstractCursorPaginator | CursorPaginatorContract | LazyCollection | Collection {
        return DataContainer::get()->dataCollectableFromSomethingResolver()->execute(
            $this->dataClass,
            $this->get(),
            $items,
            $into
        );
    }
}
