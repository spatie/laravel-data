<?php

namespace Spatie\LaravelData\Support\Validation\Rules;

use Illuminate\Validation\Rules\In;
use Spatie\LaravelData\Attributes\Validation\ObjectValidationAttribute;

class FoundationIn extends ObjectValidationAttribute
{
    public function __construct(protected In $rule)
    {
    }

    public function getRules(): array
    {
        return [$this->rule];
    }

    public static function keyword(): string
    {
        return 'in';
    }

    public static function create(string ...$parameters): static
    {
        return new self(new In($parameters));
    }
}
