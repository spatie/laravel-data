<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Spatie\LaravelData\Support\Validation\RuleDenormalizer;
use Spatie\LaravelData\Support\Validation\ValidationPath;
use Stringable;

abstract class StringValidationAttribute extends ValidationAttribute implements Stringable
{
    abstract public function parameters(): array;

    public function __toString(): string
    {
        return implode('|', app(RuleDenormalizer::class)->execute($this, ValidationPath::create()));
    }

    public static function create(string ...$parameters): static
    {
        return new static(...$parameters);
    }
}
