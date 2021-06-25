<?php

namespace Spatie\LaravelData\Transformers;

use Closure;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class DataCollectionTransformer
{
    private bool $withValueTransforming = true;

    public function __construct(
        protected string $dataClass,
        protected array $inclusionTree,
        protected array $exclusionTree,
        protected Collection|array|AbstractPaginator|AbstractCursorPaginator $items,
        protected ?Closure $through,
        protected ?Closure $filter,
    ) {
    }

    public function withoutValueTransforming(): static
    {
        $this->withValueTransforming = false;

        return $this;
    }

    public function transform(): array
    {
        if (is_array($this->items)) {
            return $this->transformCollection($this->items);
        }

        if ($this->items instanceof Collection) {
            return $this->transformCollection($this->items->all());
        }

        $this->items->through(
            $this->transformItemClosure()
        );

        return $this->withValueTransforming
            ? $this->wrapPaginatedArray($this->items->toArray())
            : $this->items->all();
    }

    private function transformCollection(array $items): array
    {
        $items = array_map($this->transformItemClosure(), $items);

        $items = $this->filter
            ? array_values(array_filter($items, $this->filter))
            : $items;

        return $this->withValueTransforming
            ? array_map(fn(Data $data) => $data->toArray(), $items)
            : $items;
    }

    private function transformItemClosure(): Closure
    {
        return function (mixed $item) {
            $item = $item instanceof Data
                ? $item
                : $this->dataClass::create($item)->withPartialsTrees($this->inclusionTree, $this->exclusionTree);

            if ($this->through) {
                $item = ($this->through)($item);
            }

            return $item;
        };
    }

    private function wrapPaginatedArray(array $paginated): array
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
