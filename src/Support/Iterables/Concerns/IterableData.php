<?php

namespace Spatie\LaravelData\Support\Iterables\Concerns;

use Spatie\LaravelData\Support\DataContainer;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;

trait IterableData
{
    public function data(
        string $dataClass,
        array|string|null $include = null,
        array|string|null $exclude = null,
        array|string|null $only = null,
        array|string|null $except = null,
        array|string|null $includePermanently = null,
        array|string|null $excludePermanently = null,
        array|string|null $onlyPermanently = null,
        array|string|null $exceptPermanently = null,
        null|bool $wrap = null,
        bool|null $transformValues = null,
        bool|null $mapPropertyNames = null,
        TransformationContextFactory $factory = null
    ): self {
        $factory = $factory ?? TransformationContextFactory::create();

        if ($include) {
            $factory->include($include);
        }

        if ($exclude) {
            $factory->exclude($exclude);
        }

        if ($only) {
            $factory->only($only);
        }

        if ($except) {
            $factory->except($except);
        }

        if ($includePermanently) {
            $factory->includePermanently($includePermanently);
        }

        if ($excludePermanently) {
            $factory->excludePermanently($excludePermanently);
        }

        if ($onlyPermanently) {
            $factory->onlyPermanently($onlyPermanently);
        }

        if ($exceptPermanently) {
            $factory->exceptPermanently($exceptPermanently);
        }

        if ($wrap === true) {
            $factory->withWrapping();
        }

        if ($wrap === false) {
            $factory->withoutWrapping();
        }

        if ($transformValues !== null) {
            $factory->withValueTransformation($transformValues);
        }

        if ($mapPropertyNames !== null) {
            $factory->withPropertyNameMapping($mapPropertyNames);
        }

        $transformationContext = null;

        foreach ($this->items as $key => $value) {
            $this->items[$key] = $value instanceof $dataClass
                ? $value
                : $dataClass::from($value);

            $transformationContext ??= $factory->create($this->items[$key]);


        }

        return $this;
    }

    public function toArray(): array
    {
        return DataContainer::get()
    }
}
