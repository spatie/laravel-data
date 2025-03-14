<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Http\Request;
use Spatie\LaravelData\Attributes\FromRouteParameter;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\Skipped;

/**
 * @deprecated Use InjectPropertyValuesPipe instead
 */
class FillRouteParameterPropertiesDataPipe implements DataPipe
{
    public function handle(
        mixed $payload,
        DataClass $class,
        array $properties,
        CreationContext $creationContext
    ): array {
        if (! $payload instanceof Request) {
            return $properties;
        }

        foreach ($class->properties as $dataProperty) {
            $attribute = $dataProperty->attributes->first(FromRouteParameter::class);

            if ($attribute === null) {
                continue;
            }

            // if inputMappedName exists, use it first
            $name = $dataProperty->inputMappedName ?: $dataProperty->name;

            if (! $attribute->shouldBeReplacedWhenPresentInPayload() && array_key_exists($name, $properties)) {
                continue;
            }

            $value = $attribute->resolve($dataProperty, $payload, $properties, $creationContext);

            if ($value === Skipped::create()) {
                continue;
            }

            $properties[$name] = $value;

            // keep the original property name
            if ($name !== $dataProperty->name) {
                $properties[$dataProperty->name] = $properties[$name];
            }
        }

        return $properties;
    }
}
