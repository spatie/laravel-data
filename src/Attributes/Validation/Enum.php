<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\Enum as EnumRule;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Enum extends ObjectValidationAttribute
{
    protected EnumRule $rule;

    public function __construct(string|EnumRule|RouteParameterReference $enum)
    {
        $this->rule = $enum instanceof EnumRule
            ? $enum
            : new EnumRule((string)$enum);
    }

    public static function keyword(): string
    {
        return 'enum';
    }

    public function getRule(ValidationPath $path): object|string
    {
        return $this->rule;
    }

    public static function create(string ...$parameters): static
    {
        return new static(new EnumRule($parameters[0]));
    }
}
