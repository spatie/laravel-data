<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Mimes extends StringValidationAttribute
{
    protected array $mimes;

    public function __construct(string|array|ExternalReference ...$mimes)
    {
        $this->mimes = Arr::flatten($mimes);
    }

    public static function keyword(): string
    {
        return 'mimes';
    }

    public function parameters(): array
    {
        return [$this->mimes];
    }
}
