<?php

namespace Spatie\LaravelData\Support\Validation;

class ValidationFieldReference
{
    public function __construct(
        public readonly string $field
    ) {
    }
}
