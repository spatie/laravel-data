<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\NotIn as BaseNotIn;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class NotIn extends ObjectValidationAttribute
{
    protected BaseNotIn $rule;

    public function __construct(array|string|BaseNotIn|RouteParameterReference ...$values)
    {
        if (count($values) === 1 && $values[0] instanceof BaseNotIn) {
            $this->rule = $values[0];

            return;
        }

        $values = array_map(
            fn (string|RouteParameterReference $value) => $this->normalizePossibleRouteReferenceParameter($value),
            Arr::flatten($values)
        );

        $this->rule = new BaseNotIn($values);
    }

    public function getRule(ValidationPath $path): object|string
    {
        return $this->rule;
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
