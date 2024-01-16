<?php

namespace Spatie\LaravelData\Support;

use ReflectionParameter;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Types\SingleType;
use Spatie\LaravelData\Support\Types\Type;

class DataParameter
{
    public function __construct(
        public readonly string $name,
        public readonly bool $isPromoted,
        public readonly bool $hasDefaultValue,
        public readonly mixed $defaultValue,
        public readonly Type $type,
        // TODO: would be better if we refactor this to type, together with Castable, Lazy, etc
        public readonly bool $isCreationContext,
    ) {
    }

    public static function create(
        ReflectionParameter $parameter,
        string $class,
    ): self {
        $hasDefaultValue = $parameter->isDefaultValueAvailable();

        $type = Type::forReflection($parameter->getType(), $class);

        return new self(
            $parameter->name,
            $parameter->isPromoted(),
            $hasDefaultValue,
            $hasDefaultValue ? $parameter->getDefaultValue() : null,
            $type,
            $type instanceof SingleType && $type->type->name === CreationContext::class
        );
    }
}
