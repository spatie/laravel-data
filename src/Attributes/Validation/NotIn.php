<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\NotIn as BaseNotIn;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NotIn implements ValidationAttribute
{
    private array $rules;

    public function __construct(array $values)
    {
        $this->rules = [ new BaseNotIn($values) ];
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
