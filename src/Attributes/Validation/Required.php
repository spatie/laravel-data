<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\RequiredIf;
use Spatie\LaravelData\Support\Validation\RequiringRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Required extends ValidationAttribute implements RequiringRule
{
    public function __construct(protected ?RequiredIf $rule = null)
    {
    }

    public function getRules(): array
    {
        return [$this->rule ?? static::keyword()];
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
