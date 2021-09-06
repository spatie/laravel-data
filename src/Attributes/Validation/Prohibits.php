<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Prohibits extends ValidationAttribute
{
    public function __construct(private array | string $fields)
    {
    }

    public function getRules(): array
    {
        return ["prohibits:{$this->normalizeValue($this->fields)}"];
    }
}
