<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class NotRegex extends StringValidationAttribute
{
    public function __construct(
        protected string|ExternalReference $pattern,
        array|string|null $context = null,
    ) {
        $this->context = $context;
    }

    public static function keyword(): string
    {
        return 'not_regex';
    }

    public function parameters(): array
    {
        return [$this->pattern];
    }
}
