<?php

namespace Spatie\LaravelData\Support\Validation\References;

use Spatie\LaravelData\Exceptions\CannotResolveRouteParameterReference;
use Stringable;

class RouteParameterReference implements Stringable
{
    public function __construct(
        public readonly string $routeParameter,
        public readonly ?string $property = null,
    ) {
    }

    public function getValue(): string
    {
        $parameter = \request()->route($this->routeParameter);

        if ($parameter === null) {
            throw CannotResolveRouteParameterReference::parameterNotFound($this->routeParameter, $this->property);
        }

        if ($this->property === null) {
            return $parameter;
        }

        $value = data_get($parameter, $this->property);

        if ($value === null) {
            throw CannotResolveRouteParameterReference::propertyOnParameterNotFound($this->routeParameter, $this->property);
        }

        return $value;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
