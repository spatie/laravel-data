<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\NotIn as BaseNotIn;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NotIn implements ValidationAttribute
{
    private array $values;

    public function __construct(array | string $values)
    {
        $this->values = is_string($values) ? [$values] : $values;
    }

    public function getRules(): array
    {
        return [new BaseNotIn($this->values)];
    }
}
