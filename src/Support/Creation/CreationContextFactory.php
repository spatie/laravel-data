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
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\DataContainer;

/**
 * @template TData
 */
class CreationContextFactory
{
    /**
     * @param class-string<TData> $dataClass
     */
    public function __construct(
        public string $dataClass,
        public ValidationStrategy $validationStrategy,
        public bool $mapPropertyNames,
        public bool $disableMagicalCreation,
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
            validationStrategy: ValidationStrategy::from($config['validation_strategy']),
            mapPropertyNames: true,
            disableMagicalCreation: false,
            ignoredMagicalMethods: null,
            casts: null,
        );
    }

    public static function createFromCreationContext(
        string $dataClass,
        CreationContext $creationContext,
    ): self {
        return new self(
            dataClass: $dataClass,
            validationStrategy: $creationContext->validationStrategy,
            mapPropertyNames: $creationContext->mapPropertyNames,
            disableMagicalCreation: $creationContext->disableMagicalCreation,
            ignoredMagicalMethods: $creationContext->ignoredMagicalMethods,
            casts: $creationContext->casts,
        );
    }

    public function validationStrategy(ValidationStrategy $validationStrategy): self
    {
        $this->validationStrategy = $validationStrategy;

        return $this;
    }

    public function withoutValidation(): self
    {
        $this->validationStrategy = ValidationStrategy::Disabled;

        return $this;
    }

    public function onlyValidateRequests(): self
    {
        $this->validationStrategy = ValidationStrategy::OnlyRequests;

        return $this;
    }

    public function alwaysValidate(): self
    {
        $this->validationStrategy = ValidationStrategy::Always;

        return $this;
    }

    public function withPropertyNameMapping(bool $withPropertyNameMapping = true): self
    {
        $this->mapPropertyNames = $withPropertyNameMapping;

        return $this;
    }

    public function withoutPropertyNameMapping(bool $withoutPropertyNameMapping = true): self
    {
        $this->mapPropertyNames = ! $withoutPropertyNameMapping;

        return $this;
    }

    public function withoutMagicalCreation(bool $withoutMagicalCreation = true): self
    {
        $this->disableMagicalCreation = $withoutMagicalCreation;

        return $this;
    }

    public function withMagicalCreation(bool $withMagicalCreation = true): self
    {
        $this->disableMagicalCreation = ! $withMagicalCreation;

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
        Cast|string $cast,
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
            mappedProperties: [],
            currentPath: [],
            validationStrategy: $this->validationStrategy,
            mapPropertyNames: $this->mapPropertyNames,
            disableMagicalCreation: $this->disableMagicalCreation,
            ignoredMagicalMethods: $this->ignoredMagicalMethods,
            casts: $this->casts,
        );
    }

    /**
     * @return TData
     */
    public function from(mixed ...$payloads)
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
    ): array|DataCollection|PaginatedDataCollection|CursorPaginatedDataCollection|Enumerable|AbstractPaginator|PaginatorContract|AbstractCursorPaginator|CursorPaginatorContract|LazyCollection|Collection {
        return DataContainer::get()->dataCollectableFromSomethingResolver()->execute(
            $this->dataClass,
            $this->get(),
            $items,
            $into
        );
    }
}
