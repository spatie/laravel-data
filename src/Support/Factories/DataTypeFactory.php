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
use Spatie\LaravelData\Support\Types\IntersectionType;
use Spatie\LaravelData\Support\Types\MultiType;
use Spatie\LaravelData\Support\Types\PartialType;
use Spatie\LaravelData\Support\Types\SingleType;
use Spatie\LaravelData\Support\Types\UndefinedType;
use Spatie\LaravelData\Support\Types\UnionType;
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

        $class = match ($property::class){
            ReflectionParameter::class => $property->getDeclaringClass()?->name,
            ReflectionProperty::class => $property->class,
        };

        return match (true) {
            $type === null => $this->buildForEmptyType(),
            $type instanceof ReflectionNamedType => $this->buildForNamedType(
                $property,
                $type,
                $class,
                $classDefinedDataCollectableAnnotation
            ),
            $type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType => $this->buildForMultiType(
                $property,
                $type,
                $class,
                $classDefinedDataCollectableAnnotation
            ),
            default => throw new TypeError('Invalid reflection type')
        };
    }

    protected function buildForEmptyType(): DataType
    {
        return new DataType(
            new UndefinedType(),
            false,
            false,
            DataTypeKind::Default,
            null,
            null
        );
    }

    protected function buildForNamedType(
        ReflectionParameter|ReflectionProperty $reflectionProperty,
        ReflectionNamedType $reflectionType,
        ?string $class,
        ?DataCollectableAnnotation $classDefinedDataCollectableAnnotation,
    ): DataType {
        $type = SingleType::create($reflectionType, $class);

        if ($type->type->isLazy()) {
            throw InvalidDataType::onlyLazy($reflectionProperty);
        }

        if ($type->type->isOptional()) {
            throw InvalidDataType::onlyOptional($reflectionProperty);
        }

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
                $type->type,
                $classDefinedDataCollectableAnnotation
            );
        }

        return new DataType(
            type: $type,
            isLazy: false,
            isOptional: false,
            kind: $kind,
            dataClass: $dataClass,
            dataCollectableClass: $dataCollectableClass
        );
    }

    protected function buildForMultiType(
        ReflectionParameter|ReflectionProperty $reflectionProperty,
        ReflectionUnionType|ReflectionIntersectionType $multiReflectionType,
        ?string $class,
        ?DataCollectableAnnotation $classDefinedDataCollectableAnnotation,
    ): DataType {
        $type = match ($multiReflectionType::class) {
            ReflectionUnionType::class => UnionType::create($multiReflectionType, $class),
            ReflectionIntersectionType::class => IntersectionType::create($multiReflectionType, $class),
        };

        $isLazy = false;
        $isOptional = false;
        $kind = DataTypeKind::Default;
        $dataClass = null;
        $dataCollectableClass = null;

        foreach ($type->types as $subType) {
            $isLazy = $isLazy || $subType->isLazy();
            $isOptional = $isOptional || $subType->isOptional();

            if (($subType->builtIn === false || $subType->name === 'array')
                && $subType->isLazy() === false
                && $subType->isOptional() === false
            ) {
                if ($kind !== DataTypeKind::Default) {
                    continue;
                }

                [
                    'kind' => $kind,
                    'dataClass' => $dataClass,
                    'dataCollectableClass' => $dataCollectableClass,
                ] = $this->resolveDataSpecificProperties(
                    $reflectionProperty,
                    $subType,
                    $classDefinedDataCollectableAnnotation
                );
            }
        }

        if ($kind->isDataObject() && $type->acceptedTypesCount() > 1) {
            throw InvalidDataType::unionWithData($reflectionProperty);
        }

        if ($kind->isDataCollectable() && $type->acceptedTypesCount() > 1) {
            throw InvalidDataType::unionWithDataCollection($reflectionProperty);
        }

        return new DataType(
            type: $type,
            isLazy: $isLazy,
            isOptional: $isOptional,
            kind: $kind,
            dataClass: $dataClass,
            dataCollectableClass: $dataCollectableClass,
        );
    }

    protected function resolveDataSpecificProperties(
        ReflectionParameter|ReflectionProperty $reflectionProperty,
        PartialType $partialType,
        ?DataCollectableAnnotation $classDefinedDataCollectableAnnotation,
    ): array {
        $kind = $partialType->getDataTypeKind();

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
                'dataClass' => $partialType->name,
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
                'dataCollectableClass' => $partialType->name,
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
}
