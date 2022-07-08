<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\ProhibitedIf;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Prohibited extends ValidationAttribute
{
    public function __construct(protected ?ProhibitedIf $rule = null)
    {
    }

    public function getRules(): array
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
