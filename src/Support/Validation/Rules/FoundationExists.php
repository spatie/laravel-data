<?php

namespace Spatie\LaravelData\Support\Validation\Rules;

use Illuminate\Validation\Rules\Exists;
use Spatie\LaravelData\Attributes\Validation\ObjectValidationAttribute;

class FoundationExists extends ObjectValidationAttribute
{
    public function __construct(protected Exists $rule)
    {
    }

    public function getRules(): array
    {
        return [$this->rule];
    }

    public static function keyword(): string
    {
        return 'exists';
    }

    public static function create(string ...$parameters): static
    {
        return new self(new Exists($parameters[0], $parameters[1] ?? null));
    }
}
