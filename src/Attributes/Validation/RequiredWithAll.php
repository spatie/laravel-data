<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RequiredWithAll implements ValidationAttribute
{
    private array $fields;

    public function __construct(string ...$fields)
    {
        $this->fields = $fields;
    }

    public function getRules(): array
    {
        return [
            'required_with_all:' . implode(',', $this->fields),
        ];
    }
}
