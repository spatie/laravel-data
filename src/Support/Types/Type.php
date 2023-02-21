<?php

namespace Spatie\LaravelData\Support\Types;

use Exception;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

abstract class Type
{
    public function __construct(
        public readonly bool $isNullable,
        public readonly bool $isMixed,
    ) {
    }

    public static function forReflection(
        ?ReflectionType $type,
        string $class,
    ): self
    {
        return match (true) {
            $type instanceof ReflectionNamedType => SingleType::create($type, $class),
            $type instanceof ReflectionUnionType => UnionType::create($type, $class),
            $type instanceof ReflectionIntersectionType => IntersectionType::create($type, $class),
            default => new UndefinedType(),
        };
    }

    abstract public function acceptsType(string $type): bool;

    abstract public function findAcceptedTypeForBaseType(string $class): ?string;

    // TODO: remove this?
    abstract public function getAcceptedTypes():  array;

    public function acceptsValue(mixed $value): bool
    {
        if ($this->isNullable && $value === null) {
            return true;
        }

        $type = gettype($value);

        $type = match ($type) {
            'integer' => 'int',
            'boolean' => 'bool',
            'double' => 'float',
            'object' => $value::class,
            default => $type,
        };

        return $this->acceptsType($type);
    }

}
