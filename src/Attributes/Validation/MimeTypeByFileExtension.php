<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class MimeTypeByFileExtension extends StringValidationAttribute
{
    public function __construct(
        private readonly array|string $types,
    ) {
    }

    public static function keyword(): string
    {
        return 'mimes';
    }

    public function parameters(): array
    {
        return Arr::wrap($this->types);
    }
}
