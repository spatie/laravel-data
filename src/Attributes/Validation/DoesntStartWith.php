<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class DoesntStartWith extends StringValidationAttribute
{
    protected string|array $values;

    public function __construct(string|array|RouteParameterReference ...$values)
    {
        $this->values = Arr::flatten($values);
    }

    public static function keyword(): string
    {
        return 'doesnt_start_with';
    }

    public function parameters(): array
    {
        return [$this->values];
    }
}
