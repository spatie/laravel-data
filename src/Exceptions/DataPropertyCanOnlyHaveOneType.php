<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;
use Spatie\LaravelData\Support\DataProperty;

class DataPropertyCanOnlyHaveOneType extends Exception
{
    public static function create(DataProperty $property)
    {
        return new self("When resolving an empty data property, it can only have one type, {$property->className}::{$property->name} has multiple types. You can overwrite this by providing an empty value for the property in the `empty` call.");
    }
}
