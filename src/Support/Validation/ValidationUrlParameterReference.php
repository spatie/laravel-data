<?php

namespace Spatie\LaravelData\Support\Validation;

class ValidationUrlParameterReference
{
    public function __construct(
        public readonly string $parameter,
        public readonly string $field,
    ) {
    }
}
