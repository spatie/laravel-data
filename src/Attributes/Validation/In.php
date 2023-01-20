<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\In as BaseIn;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY)]
class In extends ValidationAttribute
{
    protected BaseIn $rule;

    public function __construct(array|string|BaseIn ...$values)
    {
        if (count($values) === 1 && $values[0] instanceof BaseIn) {
            $this->rule = $values[0];

            return;
        }

        $this->rule = new BaseIn(Arr::flatten($values));
    }

    public function getRules(ValidationPath $path): array
    {
        return [$this->rule];
    }

    public static function keyword(): string
    {
        return 'in';
    }

    public static function create(string ...$parameters): static
    {
        return new static(new BaseIn($parameters));
    }
}
