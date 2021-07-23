<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;
use ReflectionProperty;

class InvalidDataPropertyType extends Exception
{
    public static function onlyLazy(ReflectionProperty $property)
    {
        return new self("A data property cannot have Lazy as it's only type ({$property->class}::{$property->name})");
    }

    public static function unionWithData(ReflectionProperty $property)
    {
        return new self("A data property cannot have multiple types besides the data object type ({$property->class}::{$property->name})");
    }

    public static function unionWithDataCollection(ReflectionProperty $property)
    {
        return new self("A data property cannot have multiple types besides the data object collection type ({$property->class}::{$property->name})");
    }
}
