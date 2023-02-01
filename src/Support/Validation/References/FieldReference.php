<?php

namespace Spatie\LaravelData\Support\Validation\References;

use Spatie\LaravelData\Support\Validation\ValidationPath;

class FieldReference
{
    public function __construct(
        public readonly string $name,
        public readonly bool $fromRoot = false,
    ) {
    }

    public function getValue(ValidationPath $path): string
    {
        return $this->fromRoot
            ? $this->name
            : $path->property($this->name)->get();
    }
}
