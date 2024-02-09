<?php

namespace Spatie\LaravelData\Support;

use Closure;

/**
 * @template T
 */
class LazyDataStructureProperty extends DataStructureProperty
{
    /**
     * @param Closure(): T $value
     */
    public function __construct(
        protected Closure $value
    ) {
    }

    /**
     * @return T
     */
    public function resolve()
    {
        if (! isset($this->resolved)) {
            $this->resolved = ($this->value)();
        }

        return $this->resolved;
    }

    public function toDataStructureProperty(): DataStructureProperty
    {
        $property = new DataStructureProperty();

        $property->setResolved($this->resolve());

        return $property;
    }
}
