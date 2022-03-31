<?php

namespace Spatie\LaravelData\Support;

use ReflectionParameter;
use Spatie\BetterTypes\Type;

class DataParameter
{
    public function __construct(
        public readonly string $name,
        public readonly bool $isPromoted,
        public readonly bool $hasDefaultValue,
        public readonly mixed $defaultValue,
        public readonly DataType $type,
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
            DataType::create($parameter),
        );
    }
}
