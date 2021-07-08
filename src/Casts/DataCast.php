<?php

namespace Spatie\LaravelData\Casts;

use Exception;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class DataCast implements Cast
{
    public function cast(ReflectionNamedType $reflectionType, mixed $value): Data|Uncastable
    {
        $name = $reflectionType->getName();

        if (! is_a($name, Data::class, true)) {
            return Uncastable::create();
        }

        if (! is_array($value)) {
            throw new Exception('Can only create data object from array');
        }

        $reflection = new ReflectionClass($name);

        /** @var \Spatie\LaravelData\Data $data */
        $data = collect($reflection->getProperties(ReflectionProperty::IS_PUBLIC))
            ->mapWithKeys(fn(ReflectionProperty $property) => [
                $property->getName() => $this->castValue($property, $value[$property->getName()] ?? null),
            ])
            ->pipe(fn(Collection $properties) => new $name(...$properties));

        return $data;
    }


    private function castValue(ReflectionProperty $reflectionProperty, mixed $value): mixed
    {
        $type = $this->getReflectionType($reflectionProperty);

        if ($type === null) {
            return $value;
        }

        if ($type->isBuiltin()) {
            return $value;
        }

        if ($type->allowsNull() && $value === null) {
            return $value;
        }

        foreach (app('data.casts') as $castClass) {
            /** @var \Spatie\LaravelData\Casts\Cast $cast */
            $cast = new $castClass;

            $casted = $cast->cast($type, $value);

            if (! $casted instanceof Uncastable) {
                return $casted;
            }
        }

        return $value;
    }

    private function getReflectionType(ReflectionProperty $reflectionProperty): ?ReflectionNamedType
    {
        $type = $reflectionProperty->getType();

        if ($type === null) {
            return $type;
        }

        if ($type instanceof ReflectionNamedType) {
            return $type;
        }

        if (! $type instanceof ReflectionUnionType) {
            throw new Exception();
        }

        $types = array_filter(
            $type->getTypes(),
            fn(ReflectionNamedType $childType) => $childType->getName() !== Lazy::class || $childType !== 'null'
        );

        return count($types) > 0 ? current($types) : null;
    }
}
