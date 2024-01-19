<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;

class MapPropertiesDataPipe implements DataPipe
{
    public function handle(
        mixed $payload,
        DataClass $class,
        array $properties,
        CreationContext $creationContext
    ): array {
        if ($creationContext->mapPropertyNames === false) {
            return $properties;
        }

        foreach ($class->properties as $dataProperty) {
            if ($dataProperty->inputMappedName === null) {
                continue;
            }

            if (Arr::has($properties, $dataProperty->inputMappedName)) {
                $properties[$dataProperty->name] = Arr::get($properties, $dataProperty->inputMappedName);
            }
        }

        return $properties;
    }
}
