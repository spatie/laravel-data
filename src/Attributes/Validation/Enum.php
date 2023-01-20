<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\Enum as EnumRule;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Enum extends ValidationAttribute
{
    protected EnumRule $enum;

    public function __construct(string|EnumRule $enum)
    {
        $this->enum = $enum instanceof EnumRule ? $enum : new EnumRule($enum);
    }

    public static function keyword(): string
    {
        return 'enum';
    }

    public function getRules(ValidationPath $path): array
    {
        return [$this->enum];
    }

    public static function create(string ...$parameters): static
    {
        return new static(new EnumRule($parameters[0]));
    }
}
