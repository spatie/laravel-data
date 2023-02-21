<?php

namespace Spatie\LaravelData\Resolvers;

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
use Spatie\LaravelData\Exceptions\CannotCreateDataCollectable;
use Spatie\LaravelData\Normalizers\ArrayableNormalizer;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataMethod;

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
        $from = match (gettype($items)) {
            'object' => $items::class,
            'array' => 'array',
            default => throw new Exception('Unknown type provided to create a collectable')
        };

        $into ??= $from;

        $collectable = $this->createFromCustomCreationMethod($dataClass, $items, $into);

        if ($collectable) {
            return $collectable;
        }

        if ($into === 'array') {
            return $this->createArray($dataClass, $from, $items);
        }
    }

    protected function createFromCustomCreationMethod(
        string $dataClass,
        mixed $items,
        string $into,
    ): null|array|DataCollection|PaginatedDataCollection|CursorPaginatedDataCollection|Enumerable|AbstractPaginator|Paginator|AbstractCursorPaginator|CursorPaginator {
        if ($this->withoutMagicalCreation) {
            return null;
        }

        /** @var ?DataMethod $method */
        $method = $this->dataConfig
            ->getDataClass($dataClass)
            ->methods
            ->filter(
                fn(DataMethod $method) => $method->customCreationMethodType === CustomCreationMethodType::Collection
                    && ! in_array($method->name, $this->ignoredMagicalMethods)
                    && $method->returns($into)
                    && $method->accepts([$items])
            )
            ->first();

        if ($method !== null) {
            return $dataClass::{$method->name}($items);
        }

        return null;
    }

    protected function createArray(
        string $dataClass,
        string $from,
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
                fn(mixed $data) => $dataClass::from($data),
                $items,
            );
        }

        throw CannotCreateDataCollectable::create($from, 'array');
    }
}
