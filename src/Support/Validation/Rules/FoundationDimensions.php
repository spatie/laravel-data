<?php

namespace Spatie\LaravelData\Support\Validation\Rules;

use Illuminate\Validation\Rules\Dimensions;
use Spatie\LaravelData\Attributes\Validation\Dimensions as DimensionsAttribute;
use Spatie\LaravelData\Attributes\Validation\ObjectValidationAttribute;

class FoundationDimensions extends ObjectValidationAttribute
{
    public function __construct(protected Dimensions $rule)
    {
    }

    public function getRules(): array
    {
        return [$this->rule];
    }

    public static function keyword(): string
    {
        return 'dimensions';
    }

    public static function create(string ...$parameters): static
    {
        return new DimensionsAttribute(
            $parameters['min_width'] ?? null,
            $parameters['min_height'] ?? null,
            $parameters['max_width'] ?? null,
            $parameters['max_height'] ?? null,
            $parameters['ratio'] ?? null,
            $parameters['width'] ?? null,
            $parameters['height'] ?? null,
        );
    }
}
