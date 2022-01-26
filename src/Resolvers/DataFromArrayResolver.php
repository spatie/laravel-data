<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;

class DataFromArrayResolver
{
    public function __construct(protected DataConfig $dataConfig)
    {
    }

    public function execute(string $class, array $values): Data
    {
        [$promotedProperties, $classProperties] = $this->dataConfig
            ->getDataClass($class)
            ->properties()
            ->reject(fn (DataProperty $property) => $this->shouldIgnoreProperty($property, $values))
            ->partition(fn (DataProperty $property) => $property->isPromoted());

        return $this->createDataObjectWithProperties(
            $class,
            $promotedProperties->mapWithKeys(fn (DataProperty $property) => [
                $property->name() => $this->resolveValue($property, $values),
            ]),
            $classProperties->mapWithKeys(fn (DataProperty $property) => [
                $property->name() => $this->resolveValue($property, $values),
            ])
        );
    }

    private function shouldIgnoreProperty(DataProperty $property, array $values): bool
    {
        return ! array_key_exists($property->name(), $values) && $property->hasDefaultValue();
    }

    private function resolveValue(DataProperty $property, array $values): mixed
    {
        $value = array_key_exists($property->name(), $values) ? $values[$property->name()] ?? null : Undefined::create();

        if ($value === null) {
            return $value;
        }

        if ($value instanceof Lazy) {
            return $value;
        }

        $shouldCast = $this->shouldBeCasted($property, $value);

        if ($shouldCast && $castAttribute = $property->castAttribute()) {
            return $castAttribute->get()->cast($property, $value);
        }

        if ($shouldCast && $cast = $this->dataConfig->findGlobalCastForProperty($property)) {
            return $cast->cast($property, $value);
        }

        if ($property->isData()) {
            return $property->dataClassName()::from($value);
        }

        if ($property->isDataCollection()) {
            return $property->dataClassName()::collection($value);
        }

        return $value;
    }

    private function shouldBeCasted(DataProperty $property, mixed $value): bool
    {
        $type = gettype($value);

        if ($type !== 'object') {
            return true;
        }

        return $property->types()->canBe($type);
    }

    private function createDataObjectWithProperties(
        string $class,
        Collection $promotedProperties,
        Collection $classProperties
    ): Data {
        $data = new $class(...$promotedProperties);

        $classProperties->each(
            function (mixed $value, string $name) use ($data) {
                $data->{$name} = $value;
            }
        );

        return $data;
    }
}
