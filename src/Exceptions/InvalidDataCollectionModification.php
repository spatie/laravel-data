<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;

class InvalidDataCollectionModification extends Exception
{
    public static function cannotSetItem(): self
    {
        return new self('Cannot set an item in a paginated collection');
    }

    public static function cannotUnSetItem(): self
    {
        return new self('Cannot unset an item in a paginated collection');
    }
}
