<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Exceptions\InvalidDataClassMapper;
use Spatie\LaravelData\Mappers\NameMapper;
use Spatie\LaravelData\Mappers\ProvidedNameMapper;
use Spatie\LaravelData\Support\DataClass;

class MapPropertiesDataPipe extends DataPipe
{
    public function handle(mixed $initialValue, DataClass $class, Collection $properties): Collection
    {
        $classMapper = $this->resolveClassMapper($class);

        foreach ($class->properties as $dataProperty) {
            /** @var \Spatie\LaravelData\Support\DataProperty $dataProperty */
            $mapper = $dataProperty->inputNameMapper ?? $classMapper;

            if ($mapper === null) {
                continue;
            }

            $mapped = $mapper->map($dataProperty->name);

            if (Arr::has($properties, $mapped)) {
                $properties->put($dataProperty->name, Arr::get($properties, $mapped));
            }
        }

        return $properties;
    }

    private function resolveClassMapper(DataClass $class): ?NameMapper
    {
        if ($class->inputNameMapper === null) {
            return null;
        }

        if ($class->inputNameMapper instanceof ProvidedNameMapper) {
            InvalidDataClassMapper::create($class);
        }

        return $class->inputNameMapper;
    }
}
