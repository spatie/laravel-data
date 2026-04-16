<?php

namespace Spatie\LaravelData\Support\Validation\Constraints;

use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;

abstract class DatabaseConstraint
{
    /** @param Exists|Unique $rule */
    abstract public function apply(object $rule): void;

    protected function parseExternalReference(mixed $parameter): mixed
    {
        return $parameter instanceof ExternalReference ? $parameter->getValue() : $parameter;
    }
}
