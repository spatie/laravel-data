<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;

class CannotCastEnum extends Exception
{
    public static function create(string $type, mixed $value): self
    {
        return new self("Could not cast enum: `{$value}` into a `{$type}`");
    }
}
