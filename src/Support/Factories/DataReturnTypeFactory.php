<?php

namespace Spatie\LaravelData\Support\Factories;

use ReflectionNamedType;
use ReflectionType;
use Spatie\LaravelData\Support\DataReturnType;
use Spatie\LaravelData\Support\Types\NamedType;
use Spatie\LaravelData\Support\Types\Storage\AcceptedTypesStorage;
use TypeError;

class DataReturnTypeFactory
{
    /** @var array<string, DataReturnType> */
    public static array $store = [];

    public function build(ReflectionType $type): DataReturnType
    {
        if (! $type instanceof ReflectionNamedType) {
            throw new TypeError('At the moment return types can only be of one type');
        }

        return $this->buildFromNamedType($type->getName());
    }

    public function buildFromNamedType(string $name): DataReturnType
    {
        if (array_key_exists($name, self::$store)) {
            return self::$store[$name];
        }

        $builtIn = in_array($name, ['array', 'bool', 'float', 'int', 'string', 'mixed', 'null']);

        ['acceptedTypes' => $acceptedTypes, 'kind' => $kind] = AcceptedTypesStorage::getAcceptedTypesAndKind($name);

        return static::$store[$name] = new DataReturnType(
            type: new NamedType(
                name: $name,
                builtIn: $builtIn,
                acceptedTypes: $acceptedTypes,
                kind: $kind,
                dataClass: null,
                dataCollectableClass: null,
            ),
            kind: $kind,
        );
    }

    public function buildFromValue(mixed $value): DataReturnType
    {
        return self::buildFromNamedType(get_debug_type($value));
    }
}
