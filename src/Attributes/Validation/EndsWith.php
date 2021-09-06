<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EndsWith extends ValidationAttribute
{
    public function __construct(private string | array $values)
    {
    }

    public function getRules(): array
    {
        return ["ends_with:{$this->normalizeValue($this->values)}"];
    }
}
