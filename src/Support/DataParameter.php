<?php

namespace Spatie\LaravelData\Support;

use ReflectionParameter;
use Spatie\LaravelData\Support\Types\Type;

class DataParameter
{
    public function __construct(
        public readonly string $name,
        public readonly bool $isPromoted,
        public readonly bool $hasDefaultValue,
        public readonly mixed $defaultValue,
        public readonly Type $type,
    ) {
    }

    public static function create(
        ReflectionParameter $parameter,
        string $class,
    ): self {
        $hasDefaultValue = $parameter->isDefaultValueAvailable();

        return new self(
            $parameter->name,
            $parameter->isPromoted(),
            $hasDefaultValue,
            $hasDefaultValue ? $parameter->getDefaultValue() : null,
            Type::forReflection($parameter->getType(), $class),
        );
    }
}
