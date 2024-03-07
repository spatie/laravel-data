<?php

namespace Spatie\LaravelData\Resolvers;

use Closure;
use Exception;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Enums\CustomCreationMethodType;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Exceptions\CannotCreateDataCollectable;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\Creation\CollectableMetaData;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataMethod;
use Spatie\LaravelData\Support\Factories\DataReturnTypeFactory;
use Spatie\LaravelData\Support\Types\NamedType;

class DataCollectableFromSomethingResolver
{
    public function __construct(
        protected DataConfig $dataConfig,
        protected DataFromSomethingResolver $dataFromSomethingResolver,
        protected DataReturnTypeFactory $dataReturnTypeFactory,
    ) {
    }

    public function execute(
        string $dataClass,
        CreationContext $creationContext,
        mixed $items,
        ?string $into = null,
    ): array|DataCollection|PaginatedDataCollection|CursorPaginatedDataCollection|Enumerable|AbstractPaginator|Paginator|AbstractCursorPaginator|CursorPaginator {
        $collectable = $this->createFromCustomCreationMethod($dataClass, $creationContext, $items, $into);

        if ($collectable) {
            return $collectable;
        }

        /** @var NamedType $intoType */
        $intoType = $into !== null
            ? $this->dataReturnTypeFactory->buildFromNamedType($into, $dataClass, nullable: false)->type
            : $this->dataReturnTypeFactory->buildFromValue($items, $dataClass, nullable: false)->type;

        $collectableMetaData = CollectableMetaData::fromOther($items);

        $normalizedItems = $this->normalizeItems($items, $dataClass, $creationContext);

        return match ($intoType->kind) {
            DataTypeKind::DataArray, DataTypeKind::Array => $this->normalizeToArray($normalizedItems),
            DataTypeKind::DataEnumerable, DataTypeKind::Enumerable => new $intoType->name($this->normalizeToArray($normalizedItems)),
            DataTypeKind::DataCollection => new $intoType->name($dataClass, $this->normalizeToArray($normalizedItems)),
            DataTypeKind::DataPaginatedCollection => new $intoType->name($dataClass, $this->normalizeToPaginator($normalizedItems, $collectableMetaData)),
            DataTypeKind::DataCursorPaginatedCollection => new $intoType->name($dataClass, $this->normalizeToCursorPaginator($normalizedItems, $collectableMetaData)),
            DataTypeKind::DataPaginator, DataTypeKind::Paginator => $this->normalizeToPaginator($normalizedItems, $collectableMetaData),
            DataTypeKind::DataCursorPaginator, DataTypeKind::CursorPaginator => $this->normalizeToCursorPaginator($normalizedItems, $collectableMetaData),
            default => throw CannotCreateDataCollectable::create(get_debug_type($items), $intoType->name)
        };
    }

    protected function createFromCustomCreationMethod(
        string $dataClass,
        CreationContext $creationContext,
        mixed $items,
        ?string $into,
    ): null|array|DataCollection|PaginatedDataCollection|CursorPaginatedDataCollection|Enumerable|AbstractPaginator|Paginator|AbstractCursorPaginator|CursorPaginator {
        if ($creationContext->disableMagicalCreation) {
            return null;
        }

        /** @var ?DataMethod $method */
        $method = $this->dataConfig
            ->getDataClass($dataClass)
            ->methods
            ->filter(function (DataMethod $method) use ($creationContext, $into, $items) {
                if (
                    $method->customCreationMethodType !== CustomCreationMethodType::Collection
                ) {
                    return false;
                }

                if (
                    $creationContext->ignoredMagicalMethods !== null
                    && in_array($method->name, $creationContext->ignoredMagicalMethods)
                ) {
                    return false;
                }

                if ($into !== null && ! $method->returns($into)) {
                    return false;
                }

                return $method->accepts($items);
            })
            ->first();

        if ($method === null) {
            return null;
        }

        $payload = [];

        foreach ($method->parameters as $parameter) {
            if ($parameter->type->type->isCreationContext()) {
                $payload[$parameter->name] = $creationContext;
            } else {
                $payload[$parameter->name] = $this->normalizeItems($items, $dataClass, $creationContext);
            }
        }

        return $dataClass::{$method->name}(...$payload);
    }

    protected function normalizeItems(
        mixed $items,
        string $dataClass,
        CreationContext $creationContext,
    ): array|Paginator|AbstractPaginator|CursorPaginator|AbstractCursorPaginator|Enumerable {
        if ($items instanceof PaginatedDataCollection
            || $items instanceof CursorPaginatedDataCollection
            || $items instanceof DataCollection
        ) {
            $items = $items->items();
        }

        if ($items instanceof Paginator
            || $items instanceof AbstractPaginator
            || $items instanceof CursorPaginator
            || $items instanceof AbstractCursorPaginator) {
            return $items->through($this->itemsToDataClosure($dataClass, $creationContext));
        }

        if ($items instanceof Enumerable) {
            return $items->map($this->itemsToDataClosure($dataClass, $creationContext));
        }

        if (is_array($items)) {
            $payload = [];

            foreach ($items as $index => $item) {
                $payload[$index] = $this->itemsToDataClosure($dataClass, $creationContext)($item, $index);
            }

            return $payload;
        }

        if($items === null) {
            return [];
        }

        throw new Exception('Unable to normalize items');
    }

    protected function normalizeToArray(
        array|Paginator|AbstractPaginator|CursorPaginator|AbstractCursorPaginator|Enumerable $items,
    ): array {
        if ($items instanceof Enumerable) {
            return $items->all();
        }

        return is_array($items)
            ? $items
            : $items->items();
    }

    protected function normalizeToPaginator(
        array|Paginator|AbstractPaginator|CursorPaginator|AbstractCursorPaginator $items,
        CollectableMetaData $collectableMetaData,
    ): Paginator|AbstractPaginator {
        if ($items instanceof Paginator || $items instanceof AbstractPaginator) {
            return $items;
        }

        $normalizedItems = $this->normalizeToArray($items);

        return new LengthAwarePaginator(
            $normalizedItems,
            $collectableMetaData->paginator_total ?? count($items),
            $collectableMetaData->paginator_per_page ?? 15,
        );
    }

    protected function normalizeToCursorPaginator(
        array|Paginator|AbstractPaginator|CursorPaginator|AbstractCursorPaginator $items,
        CollectableMetaData $collectableMetaData,
    ): CursorPaginator|AbstractCursorPaginator {
        if ($items instanceof CursorPaginator || $items instanceof AbstractCursorPaginator) {
            return $items;
        }

        $normalizedItems = $this->normalizeToArray($items);

        return new \Illuminate\Pagination\CursorPaginator(
            $normalizedItems,
            $collectableMetaData->paginator_per_page ?? 15,
            $collectableMetaData->paginator_cursor
        );
    }

    protected function itemsToDataClosure(
        string $dataClass,
        CreationContext $creationContext
    ): Closure {
        return function (mixed $data, int|string $index) use ($dataClass, $creationContext) {
            if ($data instanceof $dataClass) {
                return $data;
            }

            $creationContext->next($dataClass, $index);

            $data = $creationContext->from($data);

            $creationContext->previous();

            return $data;
        };
    }
}
