<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION)]
class DataValidationContext
{
    public function __construct(
        public string $context,
    ) {}
}
