<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Spatie\LaravelData\Support\Validation\ValidationPath;
use Spatie\LaravelData\Support\Validation\ValidationRule;

abstract class CustomValidationAttribute extends ValidationRule
{
    /**
     * @return array<object|string>|object|string
     */
    abstract public function getRules(ValidationPath $path): array|object|string;
}
