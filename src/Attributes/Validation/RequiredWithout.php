<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RequiredWithout implements ValidationAttribute
{
    private array $fields;

    public function __construct(string ...$fields)
    {
        $this->fields = $fields;
    }

    public function getRules(): array
    {
        return [
            'required_without:' . implode(',', $this->fields),
        ];
    }
}
