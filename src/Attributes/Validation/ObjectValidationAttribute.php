<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Spatie\LaravelData\Support\Validation\References\ExternalReference;
use Spatie\LaravelData\Support\Validation\ValidationPath;

abstract class ObjectValidationAttribute extends ValidationAttribute
{
    abstract public function getRule(ValidationPath $path): object|string;

    protected function normalizePossibleExternalReferenceParameter(mixed $parameter): mixed
    {
        if ($parameter instanceof ExternalReference) {
            return $parameter->getValue();
        }

        return $parameter;
    }
}
