<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;

class CannotResolveRouteParameterReference extends Exception
{
    public static function parameterNotFound(string $name, ?string $property): self
    {
        return $property
            ? new self("Cannot find route parameter {$name} with property {$property}")
            : new self("Cannot find route parameter {$name}");
    }

    public static function propertyOnParameterNotFound(string $name, string $property): self
    {
        return new self("Cannot find property {$property} in route parameter {$name}");
    }
}
