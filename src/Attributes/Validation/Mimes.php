<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Mimes extends StringValidationAttribute
{
    protected array $mimes;

    public function __construct(string | array ...$mimes)
    {
        $this->mimes = Arr::flatten($mimes);
    }

    public static function keyword(): string
    {
        return 'mimes';
    }

    public function parameters(): array
    {
        return [$this->normalizeValue($this->mimes)];
    }
}
