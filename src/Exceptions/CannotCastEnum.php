<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;

class CannotCastEnum extends Exception
{
    public static function create(string $type, mixed $value, string $propertyName): self
    {
        return new self("Could not cast enum (`{$propertyName}`): `{$value}` into a `{$type}`");
    }
}
