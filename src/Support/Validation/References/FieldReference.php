<?php

namespace Spatie\LaravelData\Support\Validation\References;

use Spatie\LaravelData\Support\Validation\ValidationPath;

class FieldReference
{
    public function __construct(
        public readonly string $name,
    ) {
    }

    public function getValue(ValidationPath $path): string
    {
        return $path->property($this->name)->get();
    }
}
