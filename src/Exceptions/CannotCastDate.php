<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;

class CannotCastDate extends Exception
{
    public static function create(array $formats, string $type, mixed $value): self
    {
        return new self("Could not cast date `{$value}` into a `{$type}` using formats: ".implode(', ', $formats));
    }
}
