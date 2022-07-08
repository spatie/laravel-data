<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;

class CouldNotCreateValidationRule extends Exception
{
    public static function create(string $rule): self
    {
        return new self("Could not create a validation rule for: `{$rule}`");
    }
}
