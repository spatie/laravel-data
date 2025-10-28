<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;

class CannotCreateAbstractClass extends Exception
{
    public static function morphClassWasNotResolved(
        string $originalClass,
    ): self {
        return new self("No morph data class found for {$originalClass}, the abstract class cannot be created!");
    }
}
