<?php

namespace Spatie\LaravelData\Resolvers;

use Closure;
use Exception;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
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
use Spatie\LaravelData\Support\Creation\CollectableMetaData;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataMethod;
use Spatie\LaravelData\Support\Types\PartialType;
use function Pest\Laravel\instance;

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

        $intoDataTypeKind = $intoType->getDataTypeKind();

        $collectableMetaData = CollectableMetaData::fromOther($items);

        $normalizedItems = $this->normalizeItems($items, $dataClass);

        return match ($intoDataTypeKind) {
            DataTypeKind::Array => $this->normalizeToArray($normalizedItems),
            DataTypeKind::Enumerable => new $intoType->name($this->normalizeToArray($normalizedItems)),
            DataTypeKind::DataCollection => new $intoType->name($dataClass, $this->normalizeToArray($normalizedItems)),
            DataTypeKind::DataPaginatedCollection => new $intoType->name($dataClass, $this->normalizeToPaginator($normalizedItems, $collectableMetaData)),
            DataTypeKind::DataCursorPaginatedCollection => new $intoType->name($dataClass, $this->normalizeToCursorPaginator($normalizedItems, $collectableMetaData)),
            DataTypeKind::Paginator => $this->normalizeToPaginator($normalizedItems, $collectableMetaData),
            DataTypeKind::CursorPaginator => $this->normalizeToCursorPaginator($normalizedItems, $collectableMetaData),
            default => CannotCreateDataCollectable::create(get_debug_type($items), $intoType->name)
        };
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

    protected function normalizeItems(
        mixed $items,
        string $dataClass,
    ): array|Paginator|AbstractPaginator|CursorPaginator|AbstractCursorPaginator {
        if($items instanceof PaginatedDataCollection
            || $items instanceof CursorPaginatedDataCollection
            || $items instanceof DataCollection
        ){
            $items = $items->items();
        }

        if ($items instanceof Paginator
            || $items instanceof AbstractPaginator
            || $items instanceof CursorPaginator
            || $items instanceof AbstractCursorPaginator) {
            return $items->through($this->itemsToDataClosure($dataClass));
        }

        if ($items instanceof Enumerable) {
            $items = $items->all();
        }

        if (is_array($items)) {
            return array_map(
                $this->itemsToDataClosure($dataClass),
                $items
            );
        }

        throw new Exception('Unable to normalize items');
    }

    protected function normalizeToArray(
        array|Paginator|AbstractPaginator|CursorPaginator|AbstractCursorPaginator $items,
    ): array {
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

    protected function itemsToDataClosure(string $dataClass): Closure
    {
        return fn(mixed $data) => $data instanceof $dataClass ? $data : $dataClass::from($data);
    }
}
