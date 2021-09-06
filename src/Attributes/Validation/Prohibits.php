<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Attributes\Validation\Concerns\BuildsValidationRules;

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
