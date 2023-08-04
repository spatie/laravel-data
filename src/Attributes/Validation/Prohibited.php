<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\ProhibitedIf;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Prohibited extends ObjectValidationAttribute
{
    public function __construct(protected ?ProhibitedIf $rule = null)
    {
    }

    public function getRule(ValidationPath $path): object|string
    {
        return $this->rule ?? self::keyword();
    }

    public static function keyword(): string
    {
        return 'prohibited';
    }

    public static function create(string ...$parameters): static
    {
        return new static();
    }
}
