<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;

class CannotCastScalarFromEnum extends Exception
{
    public static function create(string $output, mixed $value): self
    {
        $valueStr = get_debug_type($value);

        return new self(
            "Could not cast from property `{$valueStr}::{$output}` into a scalar value."
        );
    }
}
