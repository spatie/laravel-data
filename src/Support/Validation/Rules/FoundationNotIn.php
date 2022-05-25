<?php

namespace Spatie\LaravelData\Support\Validation\Rules;

use Illuminate\Validation\Rules\NotIn;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\ObjectValidationAttribute;

class FoundationNotIn extends ObjectValidationAttribute
{
    public function __construct(protected NotIn $rule)
    {
    }

    public function getRules(): array
    {
        return [$this->rule];
    }

    public static function keyword(): string
    {
        return 'not_in';
    }

    public static function create(string ...$parameters): static
    {
        return new self(new NotIn($parameters));
    }
}
