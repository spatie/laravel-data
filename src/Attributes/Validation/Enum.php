<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\Enum as EnumRule;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Enum extends ObjectValidationAttribute
{
    public function __construct(
        protected string|EnumRule|RouteParameterReference $enum,
        protected ?EnumRule $rule = null,
    ) {
    }

    public static function keyword(): string
    {
        return 'enum';
    }

    public function getRule(ValidationPath $path): object|string
    {
        if ($this->rule) {
            return $this->rule;
        }

        return $this->rule = $this->enum instanceof EnumRule
            ? $this->enum
            : new EnumRule((string) $this->enum);
    }

    public static function create(string ...$parameters): static
    {
        return new static(new EnumRule($parameters[0]));
    }
}
