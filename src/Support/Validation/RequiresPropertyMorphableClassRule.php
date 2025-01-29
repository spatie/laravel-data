<?php

namespace Spatie\LaravelData\Support\Validation;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Spatie\LaravelData\Attributes\Validation\ObjectValidationAttribute;
use Spatie\LaravelData\Support\DataClass;

class RequiresPropertyMorphableClassRule extends ObjectValidationAttribute implements ValidationRule
{
    public function __construct(protected DataClass $dataClass)
    {
    }

    public function getRule(ValidationPath $path): object|string
    {
        return $this;
    }

    public static function keyword(): string
    {
        return 'requires_property_morphable_class';
    }

    public static function create(string ...$parameters): static
    {
        return new static(...$parameters);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->dataClass->propertyMorphable || $this->dataClass->isAbstract) {
            $fail('The selected :attribute is invalid for morph.');
        }
    }
}
