<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Undefined;

class DataFromArrayResolver
{
    public function __construct(protected DataConfig $dataConfig)
    {
    }

    public function execute(string $class, Collection $properties): Data
    {
        [$promotedProperties, $classProperties] = $this->dataConfig
            ->getDataClass($class)
            ->properties()
            ->partition(fn (DataProperty $property) => $property->promoted);

        return $this->createDataObjectWithProperties(
            $class,
            $promotedProperties->mapWithKeys(fn (DataProperty $property) => [
                $property->name => $properties->has($property->name)
                    ? $properties->get($property->name)
                    : Undefined::create(),
            ]),
            $classProperties->mapWithKeys(fn (DataProperty $property) => [
                $property->name => $properties->has($property->name)
                    ? $properties->get($property->name)
                    : Undefined::create(),
            ])
        );
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
