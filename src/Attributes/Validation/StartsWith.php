<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StartsWith implements ValidationAttribute
{
    private array $validStartingStrings;

    public function __construct(string ...$string)
    {
        $this->validStartingStrings = $string;
    }

    public function getRules(): array
    {
        return ['starts_with:' . implode(',', $this->validStartingStrings)];
    }
}
