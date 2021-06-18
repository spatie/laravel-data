<?php

namespace Spatie\LaravelData\Transformers;

use Closure;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
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
            $items = $this->filter
                ? array_filter($this->items, $this->filter)
                : $this->items;

            return array_map(
                $this->transformItemClosure(),
                $items
            );
        }

        if ($this->items instanceof Collection) {
            return $this->items
                ->when($this->filter, fn(Collection $items) => $items->filter($this->filter))
                ->map(
                    $this->transformItemClosure()
                )->all();
        }

        $this->items->through(
            $this->transformItemClosure()
        );

        return $this->withValueTransforming
            ? $this->items->toArray()
            : $this->items->all();
    }

    private function transformItemClosure(): Closure
    {
        return function (mixed $item) {
            if ($this->through) {
                $item = ($this->through)($item);
            }

            $item = $item instanceof Data
                ? $item
                : $this->dataClass::create($item)->withPartialsTrees($this->inclusionTree, $this->exclusionTree);

            return $this->withValueTransforming ? $item->toArray() : $item;
        };
    }
}
