<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Between extends StringValidationAttribute
{
    public function __construct(protected int | float $min, protected int | float $max)
    {
    }

    public static function keyword(): string
    {
        return 'between';
    }

    public function parameters(): array
    {
        return [$this->min, $this->max];
    }
}
