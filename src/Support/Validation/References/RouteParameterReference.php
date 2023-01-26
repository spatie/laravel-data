<?php

namespace Spatie\LaravelData\Support\Validation\References;

use Spatie\LaravelData\Exceptions\CannotResolveRouteParameterReference;
use Stringable;

class RouteParameterReference implements Stringable
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $property = null,
    ) {
    }

    public function getValue(): string
    {
        $parameter = \request()?->route($this->name);

        if ($parameter === null) {
            throw CannotResolveRouteParameterReference::parameterNotFound($this->name, $this->property);
        }

        if ($this->property === null) {
            return $parameter;
        }

        $value = data_get($parameter, $this->property);

        if ($value === null) {
            throw CannotResolveRouteParameterReference::propertyOnParameterNotFound($this->name, $this->property);
        }

        return $value;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
