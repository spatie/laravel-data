<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\In as BaseIn;

#[Attribute(Attribute::TARGET_PROPERTY)]
class In implements ValidationAttribute
{
    private array $rules;

    public function __construct(array $values)
    {
        $this->rules = [ new BaseIn($values) ];
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
