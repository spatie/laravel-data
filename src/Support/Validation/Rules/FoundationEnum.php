<?php

namespace Spatie\LaravelData\Support\Validation\Rules;

use Illuminate\Validation\Rules\Enum;
use Spatie\LaravelData\Attributes\Validation\ObjectValidationAttribute;

class FoundationEnum extends ObjectValidationAttribute
{
    public function __construct(protected Enum $rule)
    {
    }

    public function getRules(): array
    {
        return [$this->rule];
    }

    public static function keyword(): string
    {
        return 'enum';
    }

    public static function create(string ...$parameters): static
    {
        return new self(new Enum($parameters[0]));
    }
}
