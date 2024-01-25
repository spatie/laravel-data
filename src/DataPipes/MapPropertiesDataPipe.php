<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataProperty;

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

            if (! Arr::has($properties, $dataProperty->inputMappedName)) {
                continue;
            }

            $properties[$dataProperty->name] = Arr::get($properties, $dataProperty->inputMappedName);
            //            Arr::forget($properties, $dataProperty->inputMappedName);

            $this->addPropertyMappingToCreationContext(
                $creationContext,
                $dataProperty
            );
        }

        return $properties;
    }

    protected function addPropertyMappingToCreationContext(
        CreationContext $creationContext,
        DataProperty $property
    ): void {
        $depth = count($creationContext->currentPath);

        $mappedProperties = &$creationContext->mappedProperties;

        for ($i = 0; $i < $depth + 1; $i++) {
            if ($i === $depth) {
                if (! isset($mappedProperties['_mappings'])) {
                    $mappedProperties['_mappings'] = [];
                }

                if ($property->type->kind->isDataCollectable() || $property->type->kind->isDataObject()) {
                    $mappedProperties['_mappings'][$property->name] = $property->inputMappedName;
                }

                $mappedProperties['_mappings'][$property->name] = $property->inputMappedName;
            } else {
                if (! isset($mappedProperties[$creationContext->currentPath[$i]])) {
                    $mappedProperties[$creationContext->currentPath[$i]] = [];
                }

                $mappedProperties = &$mappedProperties[$creationContext->currentPath[$i]];
            }
        }
    }
}
