<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;

class InvalidDataCollectionOperation extends Exception
{
    public static function create(): self
    {
        return new self('Cannot execute an array operation on this type of collection');
    }
}
