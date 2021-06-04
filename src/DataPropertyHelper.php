<?php

namespace Spatie\LaravelData;

use Exception;
use Illuminate\Support\Collection;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

class DataPropertyHelper
{
    public function __construct(private ReflectionProperty $property)
    {
    }

    public function getEmptyValue(): mixed
    {
        $type = $this->property->getType();

        if ($type === null) {
            return null;
        }

        if ($type instanceof ReflectionNamedType) {
            return $this->getValueForNamedType($type);
        }

        if ($type instanceof ReflectionUnionType) {
            return $this->getValueForUnionType($type);
        }

        throw new Exception("Unknown reflection type");
    }

    private function getValueForNamedType(
        ReflectionNamedType $type,
    ): mixed {
        $name = $type->getName();

        if ($name === 'array') {
            return [];
        }

        if ($type->isBuiltin()) {
            return null;
        }

        if (is_subclass_of($name, Data::class)) {
            /** @var \Spatie\LaravelData\Data $name */
            return $name::empty();
        }

        if ($this->isCollectionProperty($name)) {
            return [];
        }

        return null;
    }

    private function getValueForUnionType(
        ReflectionUnionType $type
    ): mixed {
        $types = $type->getTypes();

        if ($type->allowsNull() && count($types) !== 3) {
            throw new Exception("Union lazy type can only have one real type");
        }

        if ($type->allowsNull() === false && count($types) !== 2) {
            throw new Exception("Union lazy type can only have one real type");
        }

        foreach ($types as $childType) {
            if (in_array($childType->getName(), ['null', Lazy::class]) === false) {
                return $this->getValueForNamedType($childType);
            }
        }

        return null;
    }

    private function isCollectionProperty(string $name): bool
    {
        return is_a($name, Collection::class, true)
            || is_a($name, DataCollection::class, true)
            || is_a($name, PaginatedDataCollection::class, true);
    }
}
