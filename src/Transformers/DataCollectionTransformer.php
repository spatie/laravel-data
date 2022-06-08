<?php

namespace Spatie\LaravelData\Transformers;

use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\DataObject;
use Spatie\LaravelData\Contracts\IncludeableData;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Support\PartialTrees;
use Spatie\LaravelData\Support\Wrapping\Wrap;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;

class DataCollectionTransformer
{
    public function __construct(
        protected string $dataClass,
        protected bool $transformValues,
        protected WrapExecutionType $wrapExecutionType,
        protected PartialTrees $trees,
        protected Enumerable|CursorPaginator|Paginator $items,
        protected ?Closure $through,
        protected ?Closure $filter,
        protected Wrap $wrap,
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
        $items = $items->map($this->transformItemClosure())
            ->when(
                $this->filter !== null,
                fn (Enumerable $collection) => $collection->filter($this->filter)->values()
            )
            ->when(
                $this->transformValues,
                fn (Enumerable $collection) => $collection->map(fn (TransformableData $data) => $data->transform(
                    $this->transformValues,
                    $this->wrapExecutionType->shouldExecute()
                        ? WrapExecutionType::TemporarilyDisabled
                        : $this->wrapExecutionType
                ))
            )
            ->all();

        return $this->wrapExecutionType->shouldExecute()
            ? $this->wrap->wrap($items)
            : $items;
    }

    protected function transformItemClosure(): Closure
    {
        return function (BaseData $item) {
            if($item instanceof IncludeableData){
                $item->withPartialTrees($this->trees);
            }

            if ($this->through) {
                $item = ($this->through)($item);
            }

            return $item;
        };
    }

    protected function wrapPaginatedArray(array $paginated): array
    {
        $wrapKey = $this->wrap->getKey() ?? 'data';

        return [
            $wrapKey => $paginated['data'],
            'links' => $paginated['links'] ?? [],
            'meta' => Arr::except($paginated, [
                'data',
                'links',
            ]),
        ];
    }
}
