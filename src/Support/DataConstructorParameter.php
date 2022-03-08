<?php

namespace Spatie\LaravelData\Support;

use ReflectionParameter;

class DataConstructorParameter
{
    public function __construct(
        public readonly string $name,
        public readonly bool $isPromoted,
        public readonly bool $hasDefaultValue,
        public readonly mixed $defaultValue,
    ) {
    }

    public static function create(
        ReflectionParameter $parameter
    ): static {
        $hasDefaultValue = $parameter->isDefaultValueAvailable();

        return new self(
            $parameter->name,
            $parameter->isPromoted(),
            $hasDefaultValue,
            $hasDefaultValue ? $parameter->getDefaultValue() : null,
        );
    }
}
