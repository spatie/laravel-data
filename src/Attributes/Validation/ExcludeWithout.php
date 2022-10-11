<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\RequiringRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ExcludeWithout extends StringValidationAttribute implements RequiringRule
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
