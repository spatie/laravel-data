<?php

namespace Spatie\LaravelData\Concerns;

use ArrayIterator;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Spatie\LaravelData\Transformers\DataCollectableTransformer;

/**
 * @template TKey of array-key
 */
trait BaseDataCollectable
{
    public function getDataClass(): string
    {
        return $this->dataClass;
    }

    /**  @return \ArrayIterator<TKey, array> */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->transform(
            transformValues: false,
            mapPropertyNames: false,
        ));
    }

    public function count(): int
    {
        return $this->items->count();
    }

    /**
     * @return array<array>
     */
    public function transform(
        bool $transformValues = true,
        bool $mapPropertyNames = true,
        WrapExecutionType $wrapExecutionType = WrapExecutionType::Disabled,
    ): array {
        $transformer = new DataCollectableTransformer(
            $this->dataClass,
            $transformValues,
            $mapPropertyNames,
            $wrapExecutionType,
            $this->getPartialTrees(),
            $this->items,
            $this->getWrap(),
        );

        return $transformer->transform();
    }
}
