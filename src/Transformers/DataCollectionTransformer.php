<?php

namespace Spatie\LaravelData\Transformers;

use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\PartialTrees;

class DataCollectionTransformer
{
    public function __construct(
        protected string $dataClass,
        protected bool $transformValues,
        protected PartialTrees $trees,
        protected Enumerable|CursorPaginator|Paginator $items,
        protected ?Closure $through,
        protected ?Closure $filter,
    ) {
    }

    public function transform(): array
    {
        if ($this->items instanceof Enumerable) {
            return $this->transformCollection($this->items);
        }

        $this->items->through(
            $this->transformItemClosure()
        );

        return $this->transformValues
            ? $this->wrapPaginatedArray($this->items->toArray())
            : $this->items->all();
    }

    protected function transformCollection(Enumerable $items): array
    {
        return $items->map($this->transformItemClosure())
            ->when(
                $this->filter !== null,
                fn (Enumerable $collection) => $collection->filter($this->filter)->values()
            )
            ->when(
                $this->transformValues,
                fn (Enumerable $collection) => $collection->map(fn (Data $data) => $data->transform($this->transformValues))
            )
            ->all();
    }

    protected function transformItemClosure(): Closure
    {
        return function (Data $item) {
            $item->withPartialTrees($this->trees);

            if ($this->through) {
                $item = ($this->through)($item);
            }

            return $item;
        };
    }

    protected function wrapPaginatedArray(array $paginated): array
    {
        return [
            'data' => $paginated['data'],
            'links' => $paginated['links'] ?? [],
            'meta' => Arr::except($paginated, [
                'data',
                'links',
            ]),
        ];
    }
}
