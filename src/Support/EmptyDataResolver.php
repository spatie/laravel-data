<?php

namespace Spatie\LaravelData\Support;

use Exception;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionUnionType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\DataPropertyCanOnlyHaveOneType;
use Spatie\LaravelData\Lazy;

class EmptyDataResolver
{
    public static function create(ReflectionClass $class): self
    {
        return new self($class);
    }

    public function __construct(private ReflectionClass $class)
    {
    }

    public function get(array $extra = []): array
    {
        $properties = $this->class->getProperties(ReflectionProperty::IS_PUBLIC);
        $defaults = $this->resolveDefaults($extra);

        return array_reduce($properties, function (array $payload, ReflectionProperty $property) use ($defaults, $extra) {
            $name = $property->getName();

            $payload[$name] = $defaults[$name] ?? $this->getValueForProperty($property);

            return $payload;
        }, []);
    }

    private function resolveDefaults(array $extra): array
    {
        $defaultConstructorProperties = [];

        if ($this->class->hasMethod('__construct')) {
            $defaultConstructorProperties = collect($this->class->getMethod('__construct')->getParameters())
                ->filter(fn(ReflectionParameter $parameter) => $parameter->isPromoted() && $parameter->isDefaultValueAvailable())
                ->mapWithKeys(fn(ReflectionParameter $parameter) => [
                    $parameter->name => $parameter->getDefaultValue(),
                ])
                ->toArray();
        }

        return array_merge(
            $this->class->getDefaultProperties(),
            $defaultConstructorProperties,
            $extra
        );
    }

    private function getValueForProperty(ReflectionProperty $property): mixed
    {
        $type = $property->getType();

        if ($type === null) {
            return null;
        }

        if ($type instanceof ReflectionNamedType) {
            return $this->getValueForNamedType($type);
        }

        if ($type instanceof ReflectionUnionType) {
            return $this->getValueForUnionType($property, $type);
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
        ReflectionProperty $property,
        ReflectionUnionType $type
    ): mixed {
        $types = $type->getTypes();

        $this->ensureUnionTypeIsValid($property, $types, $type->allowsNull());

        foreach ($types as $childType) {
            if (in_array($childType->getName(), ['null', Lazy::class]) === false) {
                return $this->getValueForNamedType($childType);
            }
        }

        return null;
    }

    private function ensureUnionTypeIsValid(
        ReflectionProperty $property,
        array $types,
        bool $allowsNull,
    ): void {
        $types = array_map(fn(ReflectionNamedType $type) => $type->getName(), $types);
        $count = count($types);

        if (! in_array(Lazy::class, $types)) {
            throw DataPropertyCanOnlyHaveOneType::multi($property, $count);
        }

        if ($allowsNull && $count !== 3) {
            throw DataPropertyCanOnlyHaveOneType::nullableLazy($property, $count);
        }

        if ($allowsNull === false && $count !== 2) {
            throw DataPropertyCanOnlyHaveOneType::lazy($property, $count);
        }
    }

    private function isCollectionProperty(string $name): bool
    {
        return is_a($name, Collection::class, true)
            || is_a($name, DataCollection::class, true);
    }
}
