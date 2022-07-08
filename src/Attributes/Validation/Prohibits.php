<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Prohibits extends StringValidationAttribute
{
    protected string|array $fields;

    public function __construct(array | string ...$fields)
    {
        $this->fields = Arr::flatten($fields);
    }

    public static function keyword(): string
    {
        return 'prohibits';
    }

    public function parameters(): array
    {
        return [
            $this->normalizeValue($this->fields),
        ];
    }
}
