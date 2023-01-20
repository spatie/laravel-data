<?php

namespace Spatie\LaravelData\Support\Validation;

abstract class ValidationRule
{
    abstract public function getRules(?string $path): array;
}
