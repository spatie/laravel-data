<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Mimes extends ValidationAttribute
{
    private array $mimes;

    public function __construct(string | array $mimes)
    {
        $this->mimes = is_string($mimes) ? [$mimes] : $mimes;
    }

    public function getRules(): array
    {
        return ["mimes:{$this->normalizeValue($this->mimes)}"];
    }
}
