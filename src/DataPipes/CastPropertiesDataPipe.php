<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Undefined;

class CastPropertiesDataPipe extends DataPipe
{
    public function __construct(
        protected DataConfig $dataConfig,
    ) {
    }

    public function handle(mixed $initialValue, DataClass $class, Collection $properties): Collection
    {
        $castContext = $properties->all();

        foreach ($properties as $name => $value) {
            $dataProperty = $class->properties->first(fn (DataProperty $dataProperty) => $dataProperty->name === $name);

            if ($dataProperty === null) {
                continue;
            }

            if ($value === null || $value instanceof Undefined || $value instanceof Lazy) {
                continue;
            }

            $properties[$name] = $this->cast($dataProperty, $value, $castContext);
        }

        return $properties;
    }

    private function cast(
        DataProperty $property,
        mixed $value,
        array $castContext,
    ): mixed
    {
        $shouldCast = $this->shouldBeCasted($property, $value);

        if ($shouldCast && $cast = $property->cast) {
            return $cast->cast($property, $value, $castContext);
        }

        if ($shouldCast && $cast = $this->dataConfig->findGlobalCastForProperty($property)) {
            return $cast->cast($property, $value, $castContext);
        }

        if ($property->type->isDataObject) {
            return $property->type->dataClass::from($value);
        }

        if ($property->type->isDataCollection) {
            return $property->type->dataClass::collection($value);
        }

        return $value;
    }

    private function shouldBeCasted(DataProperty $property, mixed $value): bool
    {
        $type = gettype($value);

        if ($type !== 'object') {
            return true;
        }

        return $property->type->acceptsType($type);
    }
}
