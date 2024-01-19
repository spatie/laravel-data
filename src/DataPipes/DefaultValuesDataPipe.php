<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;

class DefaultValuesDataPipe implements DataPipe
{
    public function handle(
        mixed $payload,
        DataClass $class,
        Collection $properties,
        CreationContext $creationContext
    ): Collection {
        foreach ($class->properties as $name => $property) {
            if($properties->has($name)) {
                continue;
            }

            if ($property->hasDefaultValue) {
                $properties[$name] = $property->defaultValue;

                continue;
            }

            if ($property->type->isOptional) {
                $properties[$name] = Optional::create();

                continue;
            }

            if ($property->type->isNullable) {
                $properties[$name] = null;

                continue;
            }
        }

        return $properties;
    }
}
