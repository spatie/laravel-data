<?php

namespace Spatie\LaravelData\Support\Factories;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\AbstractCursorPaginator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Enumerable;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionUnionType;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\BaseDataCollectable;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Exceptions\CannotFindDataClass;
use Spatie\LaravelData\Exceptions\InvalidDataType;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\Annotations\DataCollectableAnnotation;
use Spatie\LaravelData\Support\Annotations\DataCollectableAnnotationReader;
use Spatie\LaravelData\Support\DataType;
use Spatie\LaravelData\Support\Type;
use TypeError;
use function Pest\Laravel\instance;

class DataTypeFactory
{
    public static function create(): self
    {
        return new self();
    }

    public function build(
        ReflectionParameter|ReflectionProperty $property,
        ?DataCollectableAnnotation $classDefinedDataCollectableAnnotation = null,
    ): DataType {
        $type = $property->getType();

        return match (true) {
            $type === null => $this->buildForEmptyType(),
            $type instanceof ReflectionNamedType => $this->buildForNamedType(
                $property,
                $type,
                $classDefinedDataCollectableAnnotation
            ),
            $type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType => $this->buildForMultiType(
                $property,
                $type,
                $classDefinedDataCollectableAnnotation
            ),
            default => throw new TypeError('Invalid reflection type')
        };
    }

    protected function buildForEmptyType(): DataType
    {
        $type = DataType::create(null);

        return new DataType(
            isNullable: $type->isNullable,
            isMixed: $type->isMixed,
            isLazy: false,
            isOptional: false,
            kind: DataTypeKind::Default,
            dataClass: null,
            dataCollectableClass: null,
            acceptedTypes: $type->acceptedTypes,
        );
    }

    protected function buildForNamedType(
        ReflectionParameter|ReflectionProperty $reflectionProperty,
        ReflectionNamedType $reflectionType,
        ?DataCollectableAnnotation $classDefinedDataCollectableAnnotation,
    ): DataType {
        $typeName = $reflectionType->getName();

        if (is_a($typeName, Lazy::class, true)) {
            throw InvalidDataType::onlyLazy($reflectionProperty);
        }

        if (is_a($typeName, Optional::class, true)) {
            throw InvalidDataType::onlyOptional($reflectionProperty);
        }

        $type = DataType::create($reflectionType);

        $kind = DataTypeKind::Default;
        $dataClass = null;
        $dataCollectableClass = null;

        if (! $type->isMixed) {
            [
                'kind' => $kind,
                'dataClass' => $dataClass,
                'dataCollectableClass' => $dataCollectableClass,
            ] = $this->resolveDataSpecificProperties(
                $reflectionProperty,
                $reflectionType,
                $type->acceptedTypes[$typeName],
                $classDefinedDataCollectableAnnotation
            );
        }

        return new DataType(
            isNullable: $reflectionType->allowsNull(),
            isMixed: $type->isMixed,
            isLazy: false,
            isOptional: false,
            kind: $kind,
            dataClass: $dataClass,
            dataCollectableClass: $dataCollectableClass,
            acceptedTypes: $type->acceptedTypes,
        );
    }

    protected function buildForMultiType(
        ReflectionParameter|ReflectionProperty $reflectionProperty,
        ReflectionUnionType|ReflectionIntersectionType $multiReflectionType,
        ?DataCollectableAnnotation $classDefinedDataCollectableAnnotation,
    ): DataType {
        $acceptedTypes = [];
        $isNullable = false;
        $isLazy = false;
        $isOptional = false;
        $isMixed = false;
        $kind = DataTypeKind::Default;
        $dataClass = null;
        $dataCollectableClass = null;

        foreach ($multiReflectionType->getTypes() as $reflectionType) {
            $typeName = $reflectionType->getName();

            $singleType = Type::create($reflectionType);

            $singleTypeIsLazy = is_a($typeName, Lazy::class, true);
            $singleTypeIsOptional = is_a($typeName, Optional::class, true);

            $isNullable = $isNullable || $singleType->isNullable;
            $isMixed = $isMixed || $singleType->isMixed;
            $isLazy = $isLazy || $singleTypeIsLazy;
            $isOptional = $isOptional || $singleTypeIsOptional;

            if ($typeName
                && array_key_exists($typeName, $singleType->acceptedTypes)
                && ! $singleTypeIsLazy
                && ! $singleTypeIsOptional
            ) {
                $acceptedTypes[$typeName] = $singleType->acceptedTypes[$typeName];

                if ($kind !== DataTypeKind::Default) {
                    continue;
                }

                [
                    'kind' => $kind,
                    'dataClass' => $dataClass,
                    'dataCollectableClass' => $dataCollectableClass,
                ] = $this->resolveDataSpecificProperties(
                    $reflectionProperty,
                    $reflectionType,
                    $singleType->acceptedTypes[$typeName],
                    $classDefinedDataCollectableAnnotation
                );
            }
        }

        if ($kind->isDataObject() && count($acceptedTypes) > 1) {
            throw InvalidDataType::unionWithData($reflectionProperty);
        }

        if ($kind->isDataCollectable() && count($acceptedTypes) > 1) {
            throw InvalidDataType::unionWithDataCollection($reflectionProperty);
        }

        return new DataType(
            isNullable: $isNullable,
            isMixed: false,
            isLazy: $isLazy,
            isOptional: $isOptional,
            kind: $kind,
            dataClass: $dataClass,
            dataCollectableClass: $dataCollectableClass,
            acceptedTypes: $acceptedTypes
        );
    }

    protected function resolveDataSpecificProperties(
        ReflectionParameter|ReflectionProperty $reflectionProperty,
        ReflectionNamedType $reflectionType,
        array $baseTypes,
        ?DataCollectableAnnotation $classDefinedDataCollectableAnnotation,
    ): array {
        $typeName = $reflectionType->getName();

        $kind = match (true) {
            in_array(BaseData::class, $baseTypes) => DataTypeKind::DataObject,
            $typeName === 'array' => DataTypeKind::Array,
            in_array(Enumerable::class, $baseTypes) => DataTypeKind::Enumerable,
            in_array(DataCollection::class, $baseTypes) || $typeName === DataCollection::class => DataTypeKind::DataCollection,
            in_array(PaginatedDataCollection::class, $baseTypes) || $typeName === PaginatedDataCollection::class => DataTypeKind::DataPaginatedCollection,
            in_array(CursorPaginatedDataCollection::class, $baseTypes) || $typeName === CursorPaginatedDataCollection::class => DataTypeKind::DataCursorPaginatedCollection,
            in_array(Paginator::class, $baseTypes) || in_array(AbstractPaginator::class, $baseTypes) => DataTypeKind::Paginator,
            in_array(CursorPaginator::class, $baseTypes) || in_array(AbstractCursorPaginator::class, $baseTypes) => DataTypeKind::CursorPaginator,
            default => DataTypeKind::Default,
        };

        if ($kind === DataTypeKind::Default) {
            return [
                'kind' => DataTypeKind::Default,
                'dataClass' => null,
                'dataCollectableClass' => null,
            ];
        }

        if ($kind === DataTypeKind::DataObject) {
            return [
                'kind' => DataTypeKind::DataObject,
                'dataClass' => $typeName,
                'dataCollectableClass' => null,
            ];
        }

        $dataClass = null;

        $attributes = $reflectionProperty instanceof ReflectionProperty
            ? $reflectionProperty->getAttributes(DataCollectionOf::class)
            : [];

        if ($attribute = Arr::first($attributes)) {
            $dataClass = $attribute->getArguments()[0];
        }

        $dataClass ??= $classDefinedDataCollectableAnnotation?->dataClass;

        $dataClass ??= $reflectionProperty instanceof ReflectionProperty
            ? DataCollectableAnnotationReader::create()->getForProperty($reflectionProperty)?->dataClass
            : null;

        if ($dataClass !== null) {
            return [
                'kind' => $kind,
                'dataClass' => $dataClass,
                'dataCollectableClass' => $typeName,
            ];
        }

        if (in_array($kind, [DataTypeKind::Array, DataTypeKind::Paginator, DataTypeKind::CursorPaginator, DataTypeKind::Enumerable])) {
            return [
                'kind' => DataTypeKind::Default,
                'dataClass' => null,
                'dataCollectableClass' => null,
            ];
        }

        throw CannotFindDataClass::missingDataCollectionAnotation(
            $reflectionProperty instanceof ReflectionProperty ? $reflectionProperty->class : 'unknown',
            $reflectionProperty->name
        );
    }

    protected function resolveBaseTypes(string $type): array
    {
        if (! class_exists($type)) {
            return [];
        }

        return array_unique([
            ...array_values(class_parents($type)),
            ...array_values(class_implements($type)),
        ]);
    }
}
