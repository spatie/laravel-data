<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\LaravelData\Contracts\AppendableData;
use Spatie\LaravelData\Contracts\DataObject;
use Spatie\LaravelData\Contracts\EmptyData;
use Spatie\LaravelData\Contracts\IncludeableData;
use Spatie\LaravelData\Contracts\ResponsableData;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Contracts\ValidateableData;
use Spatie\LaravelData\Contracts\WrappableData;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Mappers\ProvidedNameMapper;
use Spatie\LaravelData\Resolvers\NameMappersResolver;
use Spatie\LaravelData\Support\Annotations\DataCollectableAnnotationReader;

/**
 * @property  class-string<DataObject> $name
 * @property  Collection<string, DataProperty> $properties
 * @property  Collection<string, DataMethod> $methods
 * @property  Collection<string, object> $attributes
 * @property  array<string, \Spatie\LaravelData\Support\Annotations\DataCollectableAnnotation> $dataCollectablePropertyAnnotations
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
        public readonly array $dataCollectablePropertyAnnotations,
        public readonly ?array $allowedRequestIncludes,
        public readonly ?array $allowedRequestExcludes,
        public readonly ?array $allowedRequestOnly,
        public readonly ?array $allowedRequestExcept,
        public DataStructureProperty $outputMappedProperties,
        public DataStructureProperty $transformationFields
    ) {
    }

    public function prepareForCache(): void
    {
        if($this->outputMappedProperties instanceof LazyDataStructureProperty) {
            $this->outputMappedProperties = $this->outputMappedProperties->toDataStructureProperty();
        }

        if($this->transformationFields instanceof LazyDataStructureProperty) {
            $this->transformationFields = $this->transformationFields->toDataStructureProperty();
        }
    }
}
