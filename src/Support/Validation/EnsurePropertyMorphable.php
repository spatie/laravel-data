<?php

namespace Spatie\LaravelData\Support\Validation;

use Closure;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Spatie\LaravelData\Attributes\Validation\ObjectValidationAttribute;
use Spatie\LaravelData\Support\DataClass;

class EnsurePropertyMorphable extends ObjectValidationAttribute implements ValidationRule
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
        throw new Exception('Cannot create a requires property morphable class rule');
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->dataClass->propertyMorphable || $this->dataClass->isAbstract) {
            $fail('The selected :attribute is invalid for morph.');
        }
    }
}
