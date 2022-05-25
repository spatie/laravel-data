<?php

namespace Spatie\LaravelData\Support\Validation\Rules;

use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\ExcludeIf;
use Spatie\LaravelData\Attributes\Validation\ObjectValidationAttribute;

class FoundationExcludeIf extends ObjectValidationAttribute
{
    public function __construct(protected ExcludeIf $rule)
    {
    }

    public function getRules(): array
    {
        return [$this->rule];
    }

    public static function keyword(): string
    {
        return 'exclude';
    }

    public static function create(string ...$parameters): static
    {
        return new self(new ExcludeIf(true));
    }
}
