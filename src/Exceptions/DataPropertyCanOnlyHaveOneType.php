<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;
use ReflectionProperty;

class DataPropertyCanOnlyHaveOneType extends Exception
{
    public static function create(ReflectionProperty $property, int $count)
    {
        return new self("A data property can only have one type, {$property->class}::{$property->name} has {$count} types");
    }
}
