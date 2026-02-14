<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Url extends StringValidationAttribute
{
    protected array $protocols;

    public function __construct(
        string|array|ExternalReference ...$protocols,
    ) {
        $extracted = $this->extractContextFromVariadicValues($protocols);

        $this->protocols = Arr::flatten($extracted['values']);
        $this->context = $extracted['context'];
    }

    public static function keyword(): string
    {
        return 'url';
    }

    public function parameters(): array
    {
        return $this->protocols;
    }
}
