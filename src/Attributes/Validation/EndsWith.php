<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EndsWith extends StringValidationAttribute
{
    protected string|array $values;

    public function __construct(string | array ...$values)
    {
        $this->values = Arr::flatten($values);
    }

    public static function keyword(): string
    {
        return 'ends_with';
    }

    public function parameters(): array
    {
        return [$this->normalizeValue($this->values)];
    }
}
