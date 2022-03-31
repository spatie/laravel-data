<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\LaravelData\Support\DataProperty;

class InvalidDataPropertyType extends Exception
{
    public static function onlyLazy(ReflectionProperty|ReflectionParameter $property)
    {
        return new self("A data property/parameter cannot have Lazy as it's only type ({$property->class}::{$property->name})");
    }

    public static function onlyUndefined(ReflectionProperty|ReflectionParameter $property)
    {
        return new self("A data property/parameter cannot have Undefined as it's only type ({$property->class}::{$property->name})");
    }

    public static function unionWithData(ReflectionProperty|ReflectionParameter $property)
    {
        return new self("A data property/parameter cannot have multiple types besides the data object type ({$property->class}::{$property->name})");
    }

    public static function unionWithDataCollection(ReflectionProperty|ReflectionParameter $property)
    {
        return new self("A data property/parameter cannot have multiple types besides the data object collection type ({$property->class}::{$property->name})");
    }
}
