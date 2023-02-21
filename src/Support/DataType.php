<?php

namespace Spatie\LaravelData\Support;

use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Support\Types\Type;
use TypeError;

class DataType
{
    public function __construct(
        public readonly Type $type,
        public readonly bool $isLazy,
        public readonly bool $isOptional,
        public readonly DataTypeKind $kind,
        public readonly ?string $dataClass,
        public readonly ?string $dataCollectableClass,
    ) {
    }

    public function isNullable(): bool
    {
        return $this->type->isNullable;
    }

    public function isMixed(): bool
    {
        return $this->type->isMixed;
    }
}
