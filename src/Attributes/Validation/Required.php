<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\RequiredIf;
use Spatie\LaravelData\Support\Validation\RequiringRule;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Required extends ObjectValidationAttribute implements RequiringRule
{
    public function __construct(protected ?RequiredIf $rule = null)
    {
    }

    public function getRule(ValidationPath $path): object|string
    {
        return $this->rule ?? self::keyword();
    }

    public static function keyword(): string
    {
        return 'required';
    }

    public static function create(string ...$parameters): static
    {
        return new static();
    }
}
