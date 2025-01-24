<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Attributes\AutoWhenLoadedLazy;
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
            if (array_key_exists($name, $properties)) {
                continue;
            }

            if ($property->hasDefaultValue) {
                $properties[$name] = $property->defaultValue;

                continue;
            }

            if ($property->type->isOptional && $creationContext->useOptionalValues) {
                $properties[$name] = Optional::create();

                continue;
            }

            if ($property->autoLazy
                && $property->autoLazy instanceof AutoWhenLoadedLazy
                && $property->autoLazy->relation !== null
                && $payload instanceof Model
                && $payload->relationLoaded($property->autoLazy->relation)
            ) {
                $properties[$name] = $payload->getRelation($property->autoLazy->relation);
            }

            if ($property->type->isNullable) {
                $properties[$name] = null;
            }
        }

        return $properties;
    }
}
