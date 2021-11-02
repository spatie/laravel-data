<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;

class InvalidDataClass extends Exception
{
    public static function create(?string $class)
    {
        $message = $class === null
            ? 'Could not create a Data object, no data class was given'
            : "Could not create a Data object, `{$class}` does not implement `Data`";

        return new self($message);
    }
}
