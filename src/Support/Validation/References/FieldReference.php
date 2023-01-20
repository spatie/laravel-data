<?php

namespace Spatie\LaravelData\Support\Validation\References;

class FieldReference
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
