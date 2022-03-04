<?php

namespace Spatie\LaravelData\Pipes;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Undefined;

class CastPropertiesPipe extends Pipe
{
    public function __construct(
        protected DataConfig $dataConfig,
    ) {
    }

    public function handle(mixed $initialValue, DataClass $class, Collection $properties): Collection|Data
    {
        foreach ($properties as $name => $value) {
            $dataProperty = $class->properties()->first(fn (DataProperty $dataProperty) => $dataProperty->name() === $name);

            if ($dataProperty === null) {
                continue;
            }

            if ($value === null || $value instanceof Undefined || $value instanceof Lazy) {
                continue;
            }

            $properties[$name] = $this->cast($dataProperty, $value);
        }

        return $properties;
    }

    private function cast(DataProperty $property, mixed $value): mixed
    {
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
}
