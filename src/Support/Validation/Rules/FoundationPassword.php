<?php

namespace Spatie\LaravelData\Support\Validation\Rules;

use Exception;
use Illuminate\Validation\Rules\Password;
use Spatie\LaravelData\Attributes\Validation\ObjectValidationAttribute;

class FoundationPassword extends ObjectValidationAttribute
{
    public function __construct(protected Password $rule)
    {
    }

    public function getRules(): array
    {
        return [$this->rule];
    }

    public static function keyword(): string
    {
        return 'password';
    }

    public static function create(string ...$parameters): static
    {
        throw new Exception('Cannot create a password rule');
    }
}
