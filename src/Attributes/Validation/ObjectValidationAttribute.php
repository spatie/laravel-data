<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;
use Spatie\LaravelData\Support\Validation\ValidationPath;

abstract class ObjectValidationAttribute extends ValidationAttribute
{
    abstract public function getRule(ValidationPath $path): object|string;

    protected function normalizePossibleRouteReferenceParameter(mixed $parameter): mixed
    {
        if ($parameter instanceof RouteParameterReference) {
            return $parameter->getValue();
        }

        return $parameter;
    }
}
