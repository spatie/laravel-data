<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rules\In as BaseIn;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class In extends ObjectValidationAttribute
{
    protected ?BaseIn $rule = null;

    private array $values;

    public function __construct(
        array|string|BaseIn|ExternalReference ...$values
    ) {
        $this->values = $values;
    }

    public function getRule(ValidationPath $path): object|string
    {
        if ($this->rule) {
            return $this->rule;
        }

        if (count($this->values) === 1 && $this->values[0] instanceof BaseIn) {
            return $this->rule = $this->values[0];
        }

        $this->values = array_map(
            fn (string|ExternalReference $value) => $this->normalizePossibleExternalReferenceParameter($value),
            Arr::flatten($this->values)
        );

        return new BaseIn($this->values);
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
