<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class DateFormat extends StringValidationAttribute
{
    protected string|array $format;

    public function __construct(
        string|array|ExternalReference ...$format,
    ) {
        $extracted = $this->extractContextFromVariadicValues($format);

        $this->format = Arr::flatten($extracted['values']);
        $this->context = $extracted['context'];
    }

    public static function keyword(): string
    {
        return 'date_format';
    }

    public function parameters(): array
    {
        return [$this->format];
    }
}
