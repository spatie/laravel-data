<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Exceptions\InvalidDataClassMapper;
use Spatie\LaravelData\Mappers\Mapper;
use Spatie\LaravelData\Mappers\NameProvidedMapper;
use Spatie\LaravelData\Undefined;

class PropertiesMapper
{
    public function __construct(protected DataConfig $dataConfig)
    {
    }

    public function execute(array $properties, string $class): array
    {
        $class = $this->dataConfig->getDataClass($class);

        $mapper = $this->resolveClassMapper($class);

        return $class->properties()->mapWithKeys(fn (DataProperty $property) => [
            $property->name() => $this->resolveValueForProperty(
                $property,
                $properties,
                $mapper
            ),
        ])->all();
    }

    private function resolveClassMapper(DataClass $class): ?Mapper
    {
        if ($class->mapperAttribute() === null) {
            return null;
        }

        $mapFrom = $class->mapperAttribute()->from;

        if (! class_exists($mapFrom)) {
            InvalidDataClassMapper::create($class);
        }

        return $this->resolveMapperFromClassString($class, $mapFrom);
    }

    private function resolveValueForProperty(
        DataProperty $property,
        array $properties,
        ?Mapper $classMapper
    ): mixed {
        $mapper = $this->resolvePropertyMapper($property, $classMapper);

        $from = $mapper === null
            ? $property->name()
            : $mapper->map($property->name(), $properties);

        if (! Arr::has($properties, $from)) {
            return new Undefined();
        }

        $value = Arr::get($properties, $from);

        return $value;
    }

    private function resolvePropertyMapper(
        DataProperty $property,
        ?Mapper $classMapper
    ): ?Mapper {
        if ($property->mapperAttribute() === null) {
            return $classMapper;
        }

        $from = $property->mapperAttribute()->from;

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
