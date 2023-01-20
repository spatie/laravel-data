<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IPv4 extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'ipv4';
    }

    public function parameters(ValidationPath $path): array
    {
        return [];
    }
}
