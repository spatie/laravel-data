<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Optional;

class DefaultValuesDataPipe extends DataPipe
{
    public function handle(mixed $initialValue, DataClass $class, Collection $properties): Collection
    {
        $class
            ->properties
            ->filter(fn (DataProperty $property) => ! $properties->has($property->name))
            ->each(function (DataProperty $property) use (&$properties) {
                if ($property->hasDefaultValue) {
                    $properties[$property->name] = $property->defaultValue;
                }

                if ($property->type->isUndefinable) {
                    $properties[$property->name] = Optional::create();
                }
            });

        return $properties;
    }
}
