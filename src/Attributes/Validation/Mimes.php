<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Mimes extends ValidationAttribute
{
    private array $mimes;

    public function __construct(string | array ...$mimes)
    {
        $this->mimes = Arr::flatten($mimes);
    }

    public function getRules(): array
    {
        return ["mimes:{$this->normalizeValue($this->mimes)}"];
    }
}
