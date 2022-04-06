<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\DataClass;

class MapPropertiesDataPipe extends DataPipe
{
    public function handle(mixed $initialValue, DataClass $class, Collection $properties): Collection
    {
        foreach ($class->properties as $dataProperty) {
            if ($dataProperty->inputMappedName === null) {
                continue;
            }

            if (Arr::has($properties, $dataProperty->inputMappedName)) {
                $properties->put($dataProperty->name, Arr::get($properties, $dataProperty->inputMappedName));
            }
        }

        return $properties;
    }
}
