<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\In as BaseIn;

#[Attribute(Attribute::TARGET_PROPERTY)]
class In extends ValidationAttribute
{
    private array $values;

    public function __construct(array | string $values)
    {
        $this->values = is_string($values) ? [$values] : $values;
    }

    public function getRules(): array
    {
        return [new BaseIn($this->values)];
    }
}
