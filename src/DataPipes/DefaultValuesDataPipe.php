<?php

namespace Spatie\LaravelData\DataPipes;

use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;

class DefaultValuesDataPipe implements DataPipe
{
    public function handle(
        mixed $payload,
        DataClass $class,
        array $properties,
        CreationContext $creationContext
    ): array {
        foreach ($class->properties as $name => $property) {
            if(array_key_exists($name, $properties)) {
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
