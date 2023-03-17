<?php

namespace Spatie\LaravelData\Transformers;

use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\IncludeableData;
use Spatie\LaravelData\Support\PartialTrees;
use Spatie\LaravelData\Support\Wrapping\Wrap;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;

class DataCollectableTransformer
{
    public function __construct(
        protected string $dataClass,
        protected bool $transformValues,
        protected WrapExecutionType $wrapExecutionType,
        protected bool $mapPropertyNames,
        protected PartialTrees $trees,
        protected Enumerable|CursorPaginator|Paginator $items,
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
        $payload = [];

        foreach ($items as $key => $item) {
            $normalized = $this->transformItemClosure()($item);

            if (! $this->transformValues) {
                $payload[$key] = $normalized;

                continue;
            }

            $payload[$key] = $normalized->transform(
                $this->transformValues,
                $this->wrapExecutionType->shouldExecute()
                    ? WrapExecutionType::TemporarilyDisabled
                    : $this->wrapExecutionType,
                $this->mapPropertyNames,
            );
        }

        return $this->wrapExecutionType->shouldExecute()
            ? $this->wrap->wrap($payload)
            : $payload;
    }

    protected function transformItemClosure(): Closure
    {
        return fn (BaseData $item) => $item instanceof IncludeableData
            ? $item->withPartialTrees($this->trees)
            : $item;
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
