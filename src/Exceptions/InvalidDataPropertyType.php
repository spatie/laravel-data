<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;
use ReflectionProperty;
use Spatie\LaravelData\Support\DataProperty;

class InvalidDataPropertyType extends Exception
{
    public static function onlyLazy(ReflectionProperty $property)
    {
        return new self("A data property cannot have Lazy as it's only type ({$property->class}::{$property->name})");
    }

    public static function onlyUndefined(ReflectionProperty $property)
    {
        return new self("A data property cannot have Undefined as it's only type ({$property->class}::{$property->name})");
    }

    public static function unionWithData(DataProperty $property)
    {
        return new self("A data property cannot have multiple types besides the data object type ({$property->className}::{$property->name})");
    }

    public static function unionWithDataCollection(DataProperty $property)
    {
        return new self("A data property cannot have multiple types besides the data object collection type ({$property->className}::{$property->name})");
    }
}
