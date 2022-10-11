<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ExcludeWithout extends StringValidationAttribute
{
    public function __construct(protected string $field)
    {
    }

    public static function keyword(): string
    {
        return 'exclude_without';
    }

    public function parameters(): array
    {
        return [
            $this->normalizeValue($this->field),
        ];
    }
}
