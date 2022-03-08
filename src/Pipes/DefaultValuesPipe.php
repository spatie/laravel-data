<?php

namespace Spatie\LaravelData\Pipes;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Undefined;

class DefaultValuesPipe extends Pipe
{
    public function handle(mixed $initialValue, DataClass $class, Collection $properties): Collection|Data
    {
        $class
            ->properties
            ->filter(fn (DataProperty $property) => ! $properties->has($property->name))
            ->each(function (DataProperty $property) use (&$properties) {
                if ($property->hasDefaultValue) {
                    $properties[$property->name] = $property->defaultValue;
                }

                if ($property->isUndefinable) {
                    $properties[$property->name] = Undefined::create();
                }
            });

        return $properties;
    }
}
