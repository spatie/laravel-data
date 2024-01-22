<?php

namespace Spatie\LaravelData\Support\Factories;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Spatie\LaravelData\Support\DataType;

class DataReturnTypeFactory
{
    /** @var array<string, DataType> */
    public static array $store = [];

    public function __construct(
        protected DataTypeFactory $typeFactory
    ) {
    }

    public function build(ReflectionMethod $type, ReflectionClass|string $class): ?DataType
    {
        if (! $type->hasReturnType()) {
            return null;
        }

        $returnType = $type->getReturnType();

        if ($returnType instanceof ReflectionNamedType) {
            return $this->buildFromNamedType($returnType->getName(), $class, $returnType->allowsNull());
        }

        return $this->typeFactory->build($returnType, $class, $returnType);
    }

    public function buildFromNamedType(
        string $name,
        ReflectionClass|string $class,
        bool $nullable,
    ): DataType {
        $storedName = $name.($nullable ? '?' : '');

        if (array_key_exists($storedName, self::$store)) {
            return self::$store[$storedName];
        }

        $builtIn = in_array($name, ['array', 'bool', 'float', 'int', 'string', 'mixed', 'null']);

        $dataType = $this->typeFactory->buildFromString(
            $name,
            $class,
            $builtIn,
            $nullable
        );

        if ($name !== 'static' && $name !== 'self') {
            self::$store[$storedName] = $dataType;
        }

        return $dataType;
    }

    public function buildFromValue(
        mixed $value,
        ReflectionClass|string $class,
        bool $nullable,
    ): DataType {
        return self::buildFromNamedType(get_debug_type($value), $class, $nullable);
    }
}
