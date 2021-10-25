<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\NotIn as BaseNotIn;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NotIn extends ValidationAttribute
{
    private array $values;

    public function __construct(array | string ...$values)
    {
        $this->values = Arr::flatten($values);
    }

    public function getRules(): array
    {
        return [new BaseNotIn($this->values)];
    }
}
