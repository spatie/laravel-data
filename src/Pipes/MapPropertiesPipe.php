<?php

namespace Spatie\LaravelData\Pipes;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Exceptions\InvalidDataClassMapper;
use Spatie\LaravelData\Mappers\Mapper;
use Spatie\LaravelData\Mappers\NameProvidedMapper;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataProperty;

class MapPropertiesPipe extends Pipe
{
    public function handle(mixed $initialValue, DataClass $class, Collection $properties): Collection
    {
        $classMapper = $this->resolveClassMapper($class);

        foreach ($class->properties as $dataProperty) {
            $mapper = $this->resolvePropertyMapper($dataProperty) ?? $classMapper;

            if ($mapper === null) {
                continue;
            }

            $mapped = $mapper->map($dataProperty->name, $properties);

            if (Arr::has($properties, $mapped)) {
                $properties->put($dataProperty->name, Arr::get($properties, $mapped));
            }
        }

        return $properties;
    }

    private function resolveClassMapper(DataClass $class): ?Mapper
    {
        if ($class->mapFrom === null) {
            return null;
        }

        $mapFrom = $class->mapFrom->from;

        if (! class_exists($mapFrom)) {
            InvalidDataClassMapper::create($class);
        }

        return $this->resolveMapperFromClassString($class, $mapFrom);
    }

    private function resolvePropertyMapper(
        DataProperty $property,
    ): ?Mapper {
        if ($property->mapFrom === null) {
            return null;
        }

        $from = $property->mapFrom->from;

        if (is_int($from)) {
            return new NameProvidedMapper($from);
        }

        if (class_exists($from)) {
            return $this->resolveMapperFromClassString($property, $from);
        }

        return new NameProvidedMapper($from);
    }

    private function resolveMapperFromClassString(
        DataProperty|DataClass $target,
        string $mapperClass
    ): Mapper {
        $mapper = resolve($mapperClass);

        if (! $mapper instanceof Mapper) {
            InvalidDataClassMapper::create($target);
        }

        return $mapper;
    }
}
