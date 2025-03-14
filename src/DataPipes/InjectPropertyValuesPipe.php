<?php

namespace Spatie\LaravelData\DataPipes;

use Spatie\LaravelData\Attributes\InjectsPropertyValue;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\Skipped;

class InjectPropertyValuesPipe implements DataPipe
{
    public function handle(mixed $payload, DataClass $class, array $properties, CreationContext $creationContext): array
    {
        foreach ($class->properties as $dataProperty) {
            /** @var null|InjectsPropertyValue $attribute */
            $attribute = $dataProperty->attributes->first(InjectsPropertyValue::class);

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
