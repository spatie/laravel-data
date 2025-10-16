<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Between extends StringValidationAttribute
{
    public function __construct(protected int|float|ExternalReference $min, protected int|float|ExternalReference $max)
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
