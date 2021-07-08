<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;
use ReflectionProperty;

class DataPropertyCanOnlyHaveOneType extends Exception
{
    public static function multi(ReflectionProperty $property, int $count): self
    {
        return new self("A data property can only have one type, {$property->class}::{$property->name} has {$count} types");
    }

    public static function lazy(ReflectionProperty $property, int $count): self
    {
        return new self("A lazy data property type can only have one type besides its Lazy type, {$property->class}::{$property->name} has {$count} types");
    }

    public static function nullableLazy(ReflectionProperty $property, int $count): self
    {
        return new self("A nullable lazy data property type can only have one type besides its `Lazy|null` type, {$property->class}::{$property->name} has {$count} types");
    }
}
