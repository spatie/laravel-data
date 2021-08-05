<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;

class CannotCreateDataFromValue extends Exception
{
    public static function create(string $dataClass, mixed $value): self
    {
        $type = gettype($value);

        if ($type === 'object') {
            $type = $value::class;
        }

        return new self("Could not create a `{$dataClass}` object from value with type `{$type}`");
    }
}
