<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Spatie\LaravelData\Attributes\Concerns\NormalizesExternalReferences;
use Spatie\LaravelData\Support\Validation\ValidationPath;

abstract class ObjectValidationAttribute extends ValidationAttribute
{
    use NormalizesExternalReferences;

    abstract public function getRule(ValidationPath $path): object|string;
}
