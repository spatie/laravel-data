<?php

namespace Spatie\LaravelData\Concerns;

use ArrayIterator;
use Spatie\LaravelData\Contracts\IncludeableData as IncludeableDataContract;
use Spatie\LaravelData\Contracts\WrappableData as WrappableDataContract;
use Spatie\LaravelData\Resolvers\TransformedDataCollectionResolver;
use Spatie\LaravelData\Resolvers\TransformedDataResolver;
use Spatie\LaravelData\Support\Partials\PartialsDefinition;
use Spatie\LaravelData\Support\Transformation\DataContext;
use Spatie\LaravelData\Support\Transformation\PartialTransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;
use Spatie\LaravelData\Support\Wrapping\Wrap;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Spatie\LaravelData\Support\Wrapping\WrapType;
use Spatie\LaravelData\Transformers\DataCollectableTransformer;

/**
 * @template TKey of array-key
 * @template TValue
 */
trait BaseDataCollectable
{
    protected ?DataContext $_dataContext = null;

    /** @return class-string<TValue> */
    public function getDataClass(): string
    {
        return $this->dataClass;
    }

    /**  @return \ArrayIterator<TKey, TValue> */
    public function getIterator(): ArrayIterator
    {
        /** @var array<TValue> $data */
        $data = $this->transform(TransformationContextFactory::create()->transformValues(false));

        return new ArrayIterator($data);
    }

    public function count(): int
    {
        return $this->items->count();
    }

    public function __sleep(): array
    {
        return ['items', 'dataClass'];
    }
}
