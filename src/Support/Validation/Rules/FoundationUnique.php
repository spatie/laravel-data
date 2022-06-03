<?php

namespace Spatie\LaravelData\Support\Validation\Rules;

use Illuminate\Validation\Rules\Unique;
use Spatie\LaravelData\Attributes\Validation\ObjectValidationAttribute;

class FoundationUnique extends ObjectValidationAttribute
{
    public function __construct(protected Unique $rule)
    {
    }

    public function getRules(): array
    {
        return [$this->rule];
    }

    public static function keyword(): string
    {
        return 'unique';
    }

    public static function create(string ...$parameters): static
    {
        return new self(new Unique($parameters[0], $parameters[1]));
    }
}
