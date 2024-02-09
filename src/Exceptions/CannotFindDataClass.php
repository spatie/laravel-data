<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;

class CannotFindDataClass extends Exception
{
    public static function forTypeable(ReflectionMethod|ReflectionProperty|ReflectionParameter|string $typeable): self
    {
        if (is_string($typeable)) {
            return new self("Cannot find data class for type `{$typeable}`");
        }

        $class = $typeable->getDeclaringClass()->getName();

        $name = match (true) {
            $typeable instanceof ReflectionMethod => "method `{$class}::{{$typeable->getName()}`",
            $typeable instanceof ReflectionProperty => "property `{$class}::{{$typeable->getName()}`",
            $typeable instanceof ReflectionParameter => "parameter `{$class}::{$typeable->getDeclaringFunction()->getName()}::{$typeable->getName()}`",
        };

        return new self("Cannot find data class for {$name}");
    }
}
