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
        $class
            ->properties
            ->filter(fn (DataProperty $property) => ! $properties->has($property->name))
            ->each(function (DataProperty $property) use (&$properties) {
                if ($property->hasDefaultValue) {
                    $properties[$property->name] = $property->defaultValue;

                    return;
                }

                if ($property->type->isOptional) {
                    $properties[$property->name] = Optional::create();

                    return;
                }

                if ($property->type->isNullable) {
                    $properties[$property->name] = null;

                    return;
                }
            });

        return $properties;
    }
}
