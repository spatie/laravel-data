<?php

namespace Spatie\LaravelData\DataPipes;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;

class CastPropertiesDataPipe implements DataPipe
{
    public function __construct(
        protected DataConfig $dataConfig,
    ) {
    }

    public function handle(
        mixed $payload,
        DataClass $class,
        array $properties,
        CreationContext $creationContext
    ): array {
        foreach ($properties as $name => $value) {
            $dataProperty = $class->properties->first(fn (DataProperty $dataProperty) => $dataProperty->name === $name);

            if ($dataProperty === null) {
                continue;
            }

            if ($value === null || $value instanceof Optional || $value instanceof Lazy) {
                continue;
            }

            $properties[$name] = $this->cast($dataProperty, $value, $properties, $creationContext);
        }

        return $properties;
    }

    protected function cast(
        DataProperty $property,
        mixed $value,
        array $properties,
        CreationContext $creationContext
    ): mixed {
        $shouldCast = $this->shouldBeCasted($property, $value);

        if ($shouldCast === false) {
            return $value;
        }

        if ($casted = $this->tryCast($property->cast, $property, $value, $properties, $creationContext)) {
            return $casted;
        }

        if ($casted = $this->tryCast($creationContext->casts?->findCastForValue($property), $property, $value, $properties, $creationContext)) {
            return $casted;
        }

        if ($casted = $this->tryCast($this->dataConfig->casts->findCastForValue($property), $property, $value, $properties, $creationContext)) {
            return $casted;
        }

        if (
            $property->type->kind->isDataObject()
            || $property->type->kind->isDataCollectable()
        ) {
            $context = $creationContext->next($property->type->dataClass, $property->name);

            return $property->type->kind->isDataObject()
                ? $context->from($value)
                : $context->collect($value, $property->type->dataCollectableClass);
        }

        return $value;
    }

    protected function tryCast(
        ?Cast $cast,
        DataProperty $property,
        mixed $value,
        array $properties,
        CreationContext $creationContext
    ): mixed {
        if ($cast === null) {
            return null;
        }

        $casted = $cast->cast($property, $value, $properties, $creationContext);

        if ($casted instanceof Uncastable) {
            return null;
        }

        return $casted;
    }

    protected function shouldBeCasted(DataProperty $property, mixed $value): bool
    {
        if (gettype($value) !== 'object') {
            return true;
        }

        if ($property->type->kind->isDataCollectable()) {
            return true; // Transform everything to data objects
        }

        return $property->type->acceptsValue($value) === false;
    }
}
