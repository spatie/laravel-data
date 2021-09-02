<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EndsWith implements ValidationAttribute
{
    private array $validEndingStrings;

    public function __construct(string ...$string)
    {
        $this->validEndingStrings = $string;
    }

    public function getRules(): array
    {
        return ['ends_with:' . implode(',', $this->validEndingStrings)];
    }
}
