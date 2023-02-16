<?php

namespace Spatie\LaravelData\Concerns;

use ArrayIterator;
use Spatie\LaravelData\Contracts\IncludeableData as IncludeableDataContract;
use Spatie\LaravelData\Contracts\WrappableData as WrappableDataContract;
use Spatie\LaravelData\Resolvers\TransformedDataCollectionResolver;
use Spatie\LaravelData\Resolvers\TransformedDataResolver;
use Spatie\LaravelData\Support\Transformation\DataContext;
use Spatie\LaravelData\Support\Transformation\LocalTransformationContext;
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
        $data = $this->transform2(TransformationContextFactory::create()->transformValues(false));

        return new ArrayIterator($data);
    }

    public function count(): int
    {
        return $this->items->count();
    }

    /**
     * @return array<array|TValue>
     */
    public function transform(
        bool $transformValues = true,
        WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
        bool $mapPropertyNames = true,
    ): array {
        $transformer = new DataCollectableTransformer(
            $this->dataClass,
            $transformValues,
            $wrapExecutionType,
            $mapPropertyNames,
            $this->getPartialTrees(),
            $this->items,
            $this->getWrap(),
        );

        return $transformer->transform();
    }

    public function transform2(
        null|TransformationContextFactory|TransformationContext $context = null,
    ): array {
        if ($context === null) {
            $context = new TransformationContext();
        }

        if ($context instanceof TransformationContextFactory) {
            $context = $context->get();
        }

        return app(TransformedDataCollectionResolver::class)->execute(
            $this,
            $context->merge(LocalTransformationContext::create($this))
        );
    }

    public function __sleep(): array
    {
        return ['items', 'dataClass'];
    }

    public function getDataContext(): DataContext
    {
        if ($this->_dataContext === null) {
            return $this->_dataContext = new DataContext(
                $this instanceof IncludeableDataContract ? $this->includeProperties() : [],
                $this instanceof IncludeableDataContract ? $this->excludeProperties() : [],
                $this instanceof IncludeableDataContract ? $this->onlyProperties() : [],
                $this instanceof IncludeableDataContract ? $this->exceptProperties() : [],
                $this instanceof WrappableDataContract ? $this->getWrap() : new Wrap(WrapType::UseGlobal),
            );
        }

        return $this->_dataContext;
    }
}
