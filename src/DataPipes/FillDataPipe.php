<?php

namespace Spatie\LaravelData\DataPipes;

use Spatie\LaravelData\Attributes\FromData\FromDataAttribute;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;

class FillDataPipe implements DataPipe
{
    public function handle(mixed $payload, DataClass $class, array $properties, CreationContext $creationContext): array
    {

        foreach ($class->properties as $dataProperty) {
            /** @var null|FromDataAttribute $attribute */
            $attribute = $dataProperty->attributes->first(
                fn (object $attribute) => $attribute instanceof FromDataAttribute
            );
            if ($attribute === null) {
                continue;
            }

            $value = $attribute->resolve($dataProperty, $payload, $properties, $creationContext);
            if ($value === null) {
                continue;
            }

            $dataProperty->setValueForProperties($properties, $value);
        }

        return $properties;
    }
}
