<?php

namespace Spatie\LaravelData\Attributes\Validation;

abstract class StringValidationAttribute extends ValidationAttribute
{
    abstract public function parameters(): array;

    public static function create(string ...$parameters): static
    {
        return new static(...$parameters);
    }
}
