<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * @property  class-string $name
 * @property  Collection<string, DataProperty> $properties
 * @property  Collection<string, DataMethod> $methods
 * @property  array<string, \Spatie\LaravelData\Support\Annotations\DataIterableAnnotation> $dataCollectablePropertyAnnotations
 */
class DataClass
{
    public function __construct(
        public readonly string $name,
        public readonly Collection $properties,
        public readonly Collection $methods,
        public readonly ?DataMethod $constructorMethod,
        public readonly bool $isReadonly,
        public readonly bool $isAbstract,
        public readonly bool $propertyMorphable,
        public readonly bool $appendable,
        public readonly bool $includeable,
        public readonly bool $responsable,
        public readonly bool $transformable,
        public readonly bool $validateable,
        public readonly bool $wrappable,
        public readonly bool $emptyData,
        public readonly DataAttributesCollection $attributes,
        public readonly array $dataIterablePropertyAnnotations,
        public DataStructureProperty $allowedRequestIncludes,
        public DataStructureProperty $allowedRequestExcludes,
        public DataStructureProperty $allowedRequestOnly,
        public DataStructureProperty $allowedRequestExcept,
        public DataStructureProperty $outputMappedProperties,
        public DataStructureProperty $transformationFields
    ) {
    }

    public function prepareForCache(): void
    {
        $properties = [
            'allowedRequestIncludes',
            'allowedRequestExcludes',
            'allowedRequestOnly',
            'allowedRequestExcept',
            'outputMappedProperties',
            'transformationFields',
        ];

        foreach ($properties as $propertyName) {
            $property = $this->$propertyName;

            if ($property instanceof LazyDataStructureProperty) {
                $this->$propertyName = $property->toDataStructureProperty();
            }
        }
    }

    public function propertiesForMorph(array $properties): ?array
    {
        $requiredProperties = $this->properties->filter(fn (DataProperty $property) => $property->isForMorph)->pluck('name');
        $forMorph = Arr::only($properties, $requiredProperties->all());

        // If all required properties are not present, return null
        if (count($forMorph) !== $requiredProperties->count()) {
            return null;
        }

        return $forMorph;
    }
}
