<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;

class CannotCreateDataCollectable extends Exception
{
    public static function create(
        string $from,
        string $into
    ): self {
        return new self("Cannot create data collectable of type `{$into}` from `{$from}`");
    }
}
