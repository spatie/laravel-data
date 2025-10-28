<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;
use Spatie\LaravelData\Support\DataProperty;

class CannotCastEnum extends Exception
{
    public static function create(string $type, mixed $value, DataProperty $property): self
    {
        return new self("Could not cast enum for property {$property->className}::{$property->name} with value `{$value}` into a `{$type}`");
    }
}
