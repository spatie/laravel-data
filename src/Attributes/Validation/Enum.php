<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\Enum as EnumRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Enum extends ValidationAttribute
{
    public function __construct(
        private string $enumClass
    ) {
    }

    public function getRules(): array
    {
        return [new EnumRule($this->enumClass)];
    }
}
