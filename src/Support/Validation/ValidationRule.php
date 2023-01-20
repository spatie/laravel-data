<?php

namespace Spatie\LaravelData\Support\Validation;

abstract class ValidationRule
{
    abstract public function getRules(ValidationPath $path): array;
}
