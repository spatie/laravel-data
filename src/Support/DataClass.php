<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Collection;

/**
 * @property  class-string $name
 * @property  Collection<string, DataProperty> $properties
 * @property  Collection<string, DataMethod> $methods
 * @property  Collection<string, object> $attributes
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
        public readonly bool $appendable,
        public readonly bool $includeable,
        public readonly bool $responsable,
        public readonly bool $transformable,
        public readonly bool $validateable,
        public readonly bool $wrappable,
        public readonly bool $emptyData,
        public readonly Collection $attributes,
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
}
