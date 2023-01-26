<?php

namespace Spatie\LaravelData\Support\Validation;

class ValidationContext
{
    public function __construct(
        public ?array $payload,
        public array $fullPayload,
        public ValidationPath $path,
    ) {
    }
}
