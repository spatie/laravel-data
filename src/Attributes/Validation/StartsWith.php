<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StartsWith extends StringValidationAttribute
{
    use GenericRule;

    private string|array $values;

    public function __construct(string | array ...$values)
    {
        $this->values = Arr::flatten($values);
    }

    public function parameters(): array
    {
        return [$this->normalizeValue($this->values)];
    }
}
