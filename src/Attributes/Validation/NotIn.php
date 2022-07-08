<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\NotIn as BaseNotIn;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NotIn extends ValidationAttribute
{
    protected BaseNotIn $rule;

    public function __construct(array|string|BaseNotIn ...$values)
    {
        if (count($values) === 1 && $values[0] instanceof BaseNotIn) {
            $this->rule = $values[0];

            return;
        }

        $this->rule = new BaseNotIn(Arr::flatten($values));
    }

    public function getRules(): array
    {
        return [$this->rule];
    }

    public static function keyword(): string
    {
        return 'not_in';
    }

    public static function create(string ...$parameters): static
    {
        return new static(new BaseNotIn($parameters));
    }
}
