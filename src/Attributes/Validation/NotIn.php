<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\NotIn as BaseNotIn;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class NotIn extends ObjectValidationAttribute
{
    protected ?BaseNotIn $rule = null;

    protected array $values;

    public function __construct(array|string|BaseNotIn|ExternalReference ...$values)
    {
        $this->values = $values;
    }

    public function getRule(ValidationPath $path): object|string
    {
        if ($this->rule) {
            return $this->rule;
        }

        if (count($this->values) === 1 && $this->values[0] instanceof BaseNotIn) {
            return $this->values[0];
        }

        $this->values = array_map(
            fn (string|ExternalReference $value) => $this->normalizePossibleExternalReferenceParameter($value),
            Arr::flatten($this->values)
        );

        return  new BaseNotIn($this->values);
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
