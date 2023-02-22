<?php

namespace Spatie\LaravelData\Resolvers;

use Closure;
use Exception;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Enums\CustomCreationMethodType;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Exceptions\CannotCreateDataCollectable;
use Spatie\LaravelData\Normalizers\ArrayableNormalizer;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataMethod;
use Spatie\LaravelData\Support\Types\PartialType;

class DataCollectableFromSomethingResolver
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected DataFromSomethingResolver $dataFromSomethingResolver,
        protected bool $withoutMagicalCreation = false,
        protected array $ignoredMagicalMethods = [],
    ) {
    }

    public function withoutMagicalCreation(bool $withoutMagicalCreation = true): self
    {
        $this->withoutMagicalCreation = $withoutMagicalCreation;

        return $this;
    }

    public function ignoreMagicalMethods(string ...$methods): self
    {
        array_push($this->ignoredMagicalMethods, ...$methods);

        return $this;
    }

    public function execute(
        string $dataClass,
        mixed $items,
        ?string $into = null,
    ): array|DataCollection|PaginatedDataCollection|CursorPaginatedDataCollection|Enumerable|AbstractPaginator|Paginator|AbstractCursorPaginator|CursorPaginator {
        $intoType = $into !== null
            ? PartialType::createFromTypeString($into)
            : PartialType::createFromValue($items);

        $collectable = $this->createFromCustomCreationMethod($dataClass, $items, $into);

        if ($collectable) {
            return $collectable;
        }

        $dataTypeKind = $intoType->getDataTypeKind();

        if ($dataTypeKind === DataTypeKind::Array) {
            return $this->createArray($dataClass, $items);
        }

        if ($dataTypeKind === DataTypeKind::Enumerable) {
            return $this->createEnumerable($dataClass, $items, $intoType);
        }

        if ($dataTypeKind === DataTypeKind::DataCollection) {
            return $this->createDataCollection($dataClass, $items, $intoType);
        }

        if ($dataTypeKind === DataTypeKind::Paginator) {
            return $this->createPaginator($dataClass, $items, $intoType);
        }

        if ($dataTypeKind === DataTypeKind::DataPaginatedCollection) {
            return $this->createPaginatedDataCollection($dataClass, $items, $intoType);
        }

        if ($dataTypeKind === DataTypeKind::CursorPaginator) {
            return $this->createCursorPaginator($dataClass, $items, $intoType);
        }

        if ($dataTypeKind === DataTypeKind::DataCursorPaginatedCollection) {
            return $this->createCursorPaginatedDataCollection($dataClass, $items, $intoType);
        }

        throw CannotCreateDataCollectable::create(get_debug_type($items), $intoType->name);
    }

    protected function createFromCustomCreationMethod(
        string $dataClass,
        mixed $items,
        ?string $into,
    ): null|array|DataCollection|PaginatedDataCollection|CursorPaginatedDataCollection|Enumerable|AbstractPaginator|Paginator|AbstractCursorPaginator|CursorPaginator {
        if ($this->withoutMagicalCreation) {
            return null;
        }

        /** @var ?DataMethod $method */
        $method = $this->dataConfig
            ->getDataClass($dataClass)
            ->methods
            ->filter(function (DataMethod $method) use ($into, $items) {
                if ($method->customCreationMethodType !== CustomCreationMethodType::Collection) {
                    return false;
                }

                if ($into !== null && ! $method->returns($into)) {
                    return false;
                }

                return $method->accepts([$items]);
            })
            ->first();

        if ($method !== null) {
            return $dataClass::{$method->name}(
                array_map($this->itemsToDataClosure($dataClass), $items)
            );
        }

        return null;
    }

    protected function createArray(
        string $dataClass,
        mixed $items
    ): array {
        if ($items instanceof DataCollection) {
            $items = $items->items();
        }

        if ($items instanceof Enumerable) {
            $items = $items->all();
        }

        if (is_array($items)) {
            return array_map(
                $this->itemsToDataClosure($dataClass),
                $items,
            );
        }

        throw CannotCreateDataCollectable::create(get_debug_type($items), 'array');
    }

    protected function createEnumerable(
        string $dataClass,
        mixed $items,
        PartialType $intoType,
    ): Enumerable {
        if ($items instanceof DataCollection) {
            $items = $items->items();
        }

        if (is_array($items)) {
            return new $intoType->name($items);
        }

        if ($items instanceof Enumerable) {
            return $items->map(
                $this->itemsToDataClosure($dataClass)
            );
        }

        throw CannotCreateDataCollectable::create(get_debug_type($items), Enumerable::class);
    }

    protected function createDataCollection(
        string $dataClass,
        mixed $items,
        PartialType $intoType,
    ): DataCollection {
        if ($items instanceof Enumerable) {
            $items = $items->all();
        }

        if (is_array($items)) {
            return new $intoType->name($dataClass, $items);
        }

        if ($items instanceof DataCollection) {
            return $items->map(
                $this->itemsToDataClosure($dataClass)
            );
        }

        throw CannotCreateDataCollectable::create(get_debug_type($items), DataCollection::class);
    }

    protected function createPaginator(
        string $dataClass,
        mixed $items,
        PartialType $intoType,
    ): AbstractPaginator|Paginator {
        if ($items instanceof PaginatedDataCollection) {
            $items = $items->items();
        }

        if ($items instanceof AbstractPaginator || $items instanceof Paginator) {
            return $items->through(
                $this->itemsToDataClosure($dataClass)
            );
        }

        throw CannotCreateDataCollectable::create(get_debug_type($items), Paginator::class);
    }

    protected function createPaginatedDataCollection(
        string $dataClass,
        mixed $items,
        PartialType $intoType,
    ): PaginatedDataCollection {
        if ($items instanceof AbstractPaginator || $items instanceof Paginator) {
            return new $intoType->name($dataClass, $items);
        }

        if ($items instanceof PaginatedDataCollection) {
            return $items->through(
                $this->itemsToDataClosure($dataClass)
            );
        }

        throw CannotCreateDataCollectable::create(get_debug_type($items), PaginatedDataCollection::class);
    }

    protected function createCursorPaginator(
        string $dataClass,
        mixed $items,
        PartialType $intoType,
    ): CursorPaginator|AbstractCursorPaginator {
        if ($items instanceof CursorPaginatedDataCollection) {
            $items = $items->items();
        }

        if ($items instanceof AbstractCursorPaginator || $items instanceof CursorPaginator) {
            return $items->through(
                $this->itemsToDataClosure($dataClass)
            );
        }

        throw CannotCreateDataCollectable::create(get_debug_type($items), CursorPaginator::class);
    }

    protected function createCursorPaginatedDataCollection(
        string $dataClass,
        mixed $items,
        PartialType $intoType,
    ): CursorPaginatedDataCollection {
        if ($items instanceof AbstractCursorPaginator || $items instanceof CursorPaginator) {
            return new $intoType->name($dataClass, $items);
        }

        if ($items instanceof CursorPaginatedDataCollection) {
            return $items->through(
                $this->itemsToDataClosure($dataClass)
            );
        }

        throw CannotCreateDataCollectable::create(get_debug_type($items), CursorPaginatedDataCollection::class);
    }

    protected function itemsToDataClosure(string $dataClass): Closure
    {
        return fn(mixed $data) => $dataClass::from($data);
    }
}
