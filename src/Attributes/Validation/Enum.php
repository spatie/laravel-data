<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\Enum as EnumRule;
use Spatie\LaravelData\Support\Validation\Rules\FoundationEnum;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Enum extends FoundationEnum
{
    public function __construct(string $enumClass)
    {
        parent::__construct(new EnumRule($enumClass));
    }
}
