<?php

namespace Spatie\LaravelData\Transformers;

use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\TransformationType;

class DataCollectionTransformer
{
    public function __construct(
        protected string $dataClass,
        protected TransformationType $transformationType,
        protected array $inclusionTree,
        protected array $exclusionTree,
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

        return $this->transformationType->useTransformers()
            ? $this->wrapPaginatedArray($this->items->toArray())
            : $this->items->all();
    }

    protected function transformCollection(Enumerable $items): array
    {
        return $items->map($this->transformItemClosure())
            ->when(
                $this->filter !== null,
                fn(Enumerable $collection) => $collection->filter($this->filter)->values())
            ->when(
                $this->transformationType->useTransformers(),
                fn(Enumerable $collection) => $collection->map(fn(Data $data) => $data->transform($this->transformationType))
            )
            ->all();
    }

    protected function transformItemClosure(): Closure
    {
        return function (Data $item) {
            $item->withPartialsTrees($this->inclusionTree, $this->exclusionTree);

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
            'meta' => Arr::except($paginated, [
                'data',
                'links',
            ]),
        ];
    }
}
