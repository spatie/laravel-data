<?php

namespace Spatie\LaravelData\Support\Validation\Rules;

use Illuminate\Validation\Rules\ProhibitedIf;
use Spatie\LaravelData\Attributes\Validation\ObjectValidationAttribute;

class FoundationProhibitedIf extends ObjectValidationAttribute
{
    public function __construct(protected ProhibitedIf $rule)
    {
    }

    public function getRules(): array
    {
        return [$this->rule];
    }

    public static function keyword(): string
    {
        return 'prohibited';
    }

    public static function create(string ...$parameters): static
    {
        return new self(new ProhibitedIf(true));
    }
}
