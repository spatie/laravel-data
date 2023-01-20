<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Timezone extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'timezone';
    }

    public function parameters(): array
    {
        return [];
    }
}
