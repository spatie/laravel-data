<?php

namespace Spatie\LaravelData\Attributes\Concerns;

use Spatie\LaravelData\Support\Validation\References\ExternalReference;

trait NormalizesExternalReferences
{
    protected function normalizePossibleExternalReferenceParameter(mixed $parameter): mixed
    {
        if ($parameter instanceof ExternalReference) {
            return $parameter->getValue();
        }

        return $parameter;
    }
}
