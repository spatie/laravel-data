<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\ProhibitedIf;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Prohibited extends ValidationAttribute
{
    public function __construct(protected ?ProhibitedIf $rule = null)
    {
    }

    public function getRules(ValidationPath $path): array
    {
        return [$this->rule ?? static::keyword()];
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
