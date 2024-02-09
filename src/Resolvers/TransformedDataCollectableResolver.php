<?php

namespace Spatie\LaravelData\Resolvers;

use Closure;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Enumerable;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Contracts\WrappableData;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Support\Wrapping\Wrap;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Spatie\LaravelData\Support\Wrapping\WrapType;

class TransformedDataCollectableResolver
{
    public function __construct(
        protected DataConfig $dataConfig
    ) {
    }

    public function execute(
        iterable $items,
        TransformationContext $context,
    ): array {
        $wrap = $items instanceof WrappableData
            ? $items->getWrap()
            : new Wrap(WrapType::UseGlobal);

        $executeWrap = $context->wrapExecutionType->shouldExecute();

        $nestedContext = $executeWrap
            ? $context->setWrapExecutionType(WrapExecutionType::TemporarilyDisabled)
            : $context;

        if ($items instanceof DataCollection) {
            return $this->transformItems($items->items(), $wrap, $executeWrap, $nestedContext);
        }

        if ($items instanceof Enumerable || is_array($items)) {
            return $this->transformItems($items, $wrap, $executeWrap, $nestedContext);
        }

        if ($items instanceof PaginatedDataCollection || $items instanceof CursorPaginatedDataCollection) {
            return $this->transformPaginator($items->items(), $wrap, $nestedContext);
        }

        if ($items instanceof Paginator || $items instanceof CursorPaginator) {
            return $this->transformPaginator($items, $wrap, $nestedContext);
        }

        throw new Exception("Cannot transform collection");
    }

    protected function transformItems(
        Enumerable|array $items,
        Wrap $wrap,
        bool $executeWrap,
        TransformationContext $nestedContext,
    ): array {
        $collection = [];

        foreach ($items as $key => $value) {
            $collection[$key] = $this->transformationClosure($nestedContext)($value);
        }

        return $executeWrap
            ? $wrap->wrap($collection)
            : $collection;
    }

    protected function transformPaginator(
        Paginator|CursorPaginator $paginator,
        Wrap $wrap,
        TransformationContext $nestedContext,
    ): array {
        $paginator = $paginator->through(fn (BaseData $data) => $this->transformationClosure($nestedContext)($data));

        if ($nestedContext->transformValues === false) {
            return $paginator->all();
        }

        $paginated = $paginator->toArray();

        $wrapKey = $wrap->getKey() ?? 'data';

        return [
            $wrapKey => $paginated['data'],
            'links' => $paginated['links'] ?? [],
            'meta' => Arr::except($paginated, [
                'data',
                'links',
            ]),
        ];
    }

    protected function transformationClosure(
        TransformationContext $nestedContext,
    ): Closure {
        return function (BaseData $data) use ($nestedContext) {
            if (! $data instanceof TransformableData || ! $nestedContext->transformValues) {
                return $data;
            }

            return $data->transform(clone $nestedContext);
        };
    }
}
