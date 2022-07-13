<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;

class CastPropertiesDataPipe implements DataPipe
{
    public function __construct(
        protected DataConfig $dataConfig,
    ) {
    }

    public function handle(mixed $payload, DataClass $class, Collection $properties): Collection
    {
        $castContext = $properties->all();

        foreach ($properties as $name => $value) {
            $dataProperty = $class->properties->first(fn (DataProperty $dataProperty) => $dataProperty->name === $name);

            if ($dataProperty === null) {
                continue;
            }

            if ($value === null || $value instanceof Optional || $value instanceof Lazy) {
                continue;
            }

            $properties[$name] = $this->cast($dataProperty, $value, $castContext);
        }

        return $properties;
    }

    protected function cast(
        DataProperty $property,
        mixed $value,
        array $castContext,
    ): mixed {
        $shouldCast = $this->shouldBeCasted($property, $value);

        if ($shouldCast === false) {
            return $value;
        }

        if ($cast = $property->cast) {
            return $cast->cast($property, $value, $castContext);
        }

        if ($cast = $this->dataConfig->findGlobalCastForProperty($property)) {
            return $cast->cast($property, $value, $castContext);
        }

        if ($property->type->isDataObject) {
            return $property->type->dataClass::from($value);
        }

        if ($property->type->isDataCollectable) {
            return $property->type->dataClass::collection($value);
        }

        return $value;
    }

    protected function shouldBeCasted(DataProperty $property, mixed $value): bool
    {
        return gettype($value) === 'object'
            ? ! $property->type->acceptsValue($value)
            : true;
    }
}
