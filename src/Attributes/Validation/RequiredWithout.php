<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Validation\RequiringRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RequiredWithout extends StringValidationAttribute implements RequiringRule
{
    private string|array $fields;

    public function __construct(
        array | string ...$fields,
    ) {
        $this->fields = Arr::flatten($fields);
    }

    public static function keyword(): string
    {
        return 'required_without';
    }

    public function parameters(): array
    {
        return [
            $this->normalizeValue($this->fields),
        ];
    }
}
