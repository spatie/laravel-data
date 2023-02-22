<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataProperty;

class DefaultValuesDataPipe implements DataPipe
{
    public function handle(mixed $payload, DataClass $class, Collection $properties): Collection
    {
        $dataDefaults = $class->defaultable
            ? app()->call([$class->name, 'defaults'])
            : [];

        $class
            ->properties
            ->filter(fn(DataProperty $property) => ! $properties->has($property->name))
            ->each(function (DataProperty $property) use ($dataDefaults, &$properties) {
                if (array_key_exists($property->name, $dataDefaults)) {
                    $properties[$property->name] = $dataDefaults[$property->name];

                    return;
                }

                if ($property->hasDefaultValue) {
                    $properties[$property->name] = $property->defaultValue;

                    return;
                }

                if ($property->type->isOptional) {
                    $properties[$property->name] = Optional::create();

                    return;
                }

                if ($property->type->isNullable()) {
                    $properties[$property->name] = null;

                    return;
                }
            });

        return $properties;
    }
}
