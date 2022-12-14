<?php

namespace Spatie\LaravelData\Exceptions;

use RuntimeException;

class CannotBuildRelativeRules extends RuntimeException
{
    public static function shouldThrow(): bool
    {
        return ! class_exists('Illuminate\\Validation\\NestedRules');
    }

    public static function create(): self
    {
        return new self('To enable support for relative rule generation, please upgrade laravel.');
    }
}
