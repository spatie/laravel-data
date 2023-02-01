<?php

namespace Spatie\LaravelData\Concerns;

use ArrayIterator;
use Spatie\LaravelData\Support\Wrapping\WrapExecutionType;
use Spatie\LaravelData\Transformers\DataCollectableTransformer;

/**
 * @template TKey of array-key
 * @template TValue
 */
trait BaseDataCollectable
{
    /** @return class-string<TValue> */
    public function getDataClass(): string
    {
        return $this->dataClass;
    }

    /**  @return \ArrayIterator<TKey, TValue> */
    public function getIterator(): ArrayIterator
    {
        /** @var array<TValue> $data */
        $data = $this->transform(transformValues: false);

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

    public function __sleep(): array
    {
        return ['items', 'dataClass'];
    }
}
