<?php

namespace Spatie\LaravelData\Transformers;

use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\TransformationType;

class DataCollectionTransformer
{
    public function __construct(
        protected string $dataClass,
        protected TransformationType $transformationType,
        protected array $inclusionTree,
        protected array $exclusionTree,
        protected array | CursorPaginator | Paginator  $items,
        protected ?Closure $through,
        protected ?Closure $filter,
    ) {
    }

    public function transform(): array
    {
        if (is_array($this->items)) {
            return $this->transformCollection($this->items);
        }

        $this->items->through(
            $this->transformItemClosure()
        );

        return $this->transformationType->useTransformers()
            ? $this->wrapPaginatedArray($this->items->toArray())
            : $this->items->all();
    }

    protected function transformCollection(array $items): array
    {
        $items = array_map($this->transformItemClosure(), $items);

        $items = $this->filter
            ? array_values(array_filter($items, $this->filter))
            : $items;

        return $this->transformationType->useTransformers()
            ? array_map(fn (Data $data) => $data->transform($this->transformationType), $items)
            : $items;
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
