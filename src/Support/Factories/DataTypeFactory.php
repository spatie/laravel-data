<?php

namespace Spatie\LaravelData\Support\Factories;

use Exception;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Exceptions\CannotFindDataClass;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Annotations\DataCollectableAnnotation;
use Spatie\LaravelData\Support\Annotations\DataCollectableAnnotationReader;
use Spatie\LaravelData\Support\DataType;
use Spatie\LaravelData\Support\Factories\Concerns\RequiresTypeInformation;
use Spatie\LaravelData\Support\Lazy\ClosureLazy;
use Spatie\LaravelData\Support\Lazy\ConditionalLazy;
use Spatie\LaravelData\Support\Lazy\DefaultLazy;
use Spatie\LaravelData\Support\Lazy\InertiaLazy;
use Spatie\LaravelData\Support\Lazy\RelationalLazy;
use Spatie\LaravelData\Support\Types\IntersectionType;
use Spatie\LaravelData\Support\Types\NamedType;
use Spatie\LaravelData\Support\Types\Storage\AcceptedTypesStorage;
use Spatie\LaravelData\Support\Types\Type;
use Spatie\LaravelData\Support\Types\UnionType;
use TypeError;

class DataTypeFactory
{
    public function __construct(
        protected DataCollectableAnnotationReader $dataCollectableAnnotationReader,
    ) {
    }

    public function build(
        ?ReflectionType $reflectionType,
        ReflectionClass|string $reflectionClass,
        ReflectionMethod|ReflectionProperty|ReflectionParameter|string $typeable,
        ?Collection $attributes = null,
        ?DataCollectableAnnotation $classDefinedDataCollectableAnnotation = null,
    ): DataType {
        $properties = match (true) {
            $reflectionType === null => $this->inferPropertiesForNoneType(),
            $reflectionType instanceof ReflectionNamedType => $this->inferPropertiesForSingleType(
                $reflectionType,
                $reflectionClass,
                $typeable,
                $attributes,
                $classDefinedDataCollectableAnnotation
            ),
            $reflectionType instanceof ReflectionUnionType || $reflectionType instanceof ReflectionIntersectionType => $this->inferPropertiesForCombinationType(
                $reflectionType,
                $reflectionClass,
                $typeable,
                $attributes,
                $classDefinedDataCollectableAnnotation
            ),
            default => throw new TypeError('Invalid reflection type')
        };

        return new DataType(
            type: $properties['type'],
            isOptional: $properties['isOptional'],
            isNullable: $reflectionType?->allowsNull() ?? true,
            isMixed: $properties['isMixed'],
            lazyType: $properties['lazyType'],
            kind: $properties['kind'],
            dataClass: $properties['dataClass'],
            dataCollectableClass: $properties['dataCollectableClass'],
        );
    }

    /**
     * @return array{
     *     type: NamedType,
     *     isMixed: bool,
     *     lazyType: ?string,
     *     isOptional: bool,
     *     kind: DataTypeKind,
     *     dataClass: ?string,
     *     dataCollectableClass: ?string
     * }
     */
    protected function inferPropertiesForNoneType(): array
    {
        $type = new NamedType(
            name: 'mixed',
            builtIn: true,
            acceptedTypes: [],
            kind: DataTypeKind::Default,
            dataClass: null,
            dataCollectableClass: null,
        );

        return [
            'type' => $type,
            'isMixed' => true,
            'isOptional' => false,
            'lazyType' => null,
            'kind' => DataTypeKind::Default,
            'dataClass' => null,
            'dataCollectableClass' => null,
        ];
    }

    /**
     * @return array{
     *      type: NamedType,
     *      isMixed: bool,
     *      lazyType: ?string,
     *      isOptional: bool,
     *      kind: DataTypeKind,
     *      dataClass: ?string,
     *      dataCollectableClass: ?string
     *  }
     *
     */
    protected function inferPropertiesForSingleType(
        ReflectionNamedType $reflectionType,
        ReflectionClass|string $reflectionClass,
        ReflectionMethod|ReflectionProperty|ReflectionParameter|string $typeable,
        ?Collection $attributes,
        ?DataCollectableAnnotation $classDefinedDataCollectableAnnotation,
    ): array {
        return [
            ...$this->inferPropertiesForNamedType(
                $reflectionType->getName(),
                $reflectionType->isBuiltin(),
                $reflectionClass,
                $typeable,
                $attributes,
                $classDefinedDataCollectableAnnotation
            ),
            'isOptional' => false,
            'lazyType' => null,
        ];
    }

    /**
     * @return array{
     *      type: NamedType,
     *      isMixed: bool,
     *      kind: DataTypeKind,
     *      dataClass: ?string,
     *      dataCollectableClass: ?string
     *  }
     */
    protected function inferPropertiesForNamedType(
        string $name,
        bool $builtIn,
        ReflectionClass|string $reflectionClass,
        ReflectionMethod|ReflectionProperty|ReflectionParameter|string $typeable,
        ?Collection $attributes,
        ?DataCollectableAnnotation $classDefinedDataCollectableAnnotation,
    ): array {
        if ($name === 'self' || $name === 'static') {
            $name = is_string($reflectionClass) ? $reflectionClass : $reflectionClass->getName();
        }

        $isMixed = $name === 'mixed';

        ['acceptedTypes' => $acceptedTypes, 'kind' => $kind] = AcceptedTypesStorage::getAcceptedTypesAndKind($name);

        if ($kind === DataTypeKind::Default) {
            return [
                'type' => new NamedType(
                    name: $name,
                    builtIn: $builtIn,
                    acceptedTypes: $acceptedTypes,
                    kind: $kind,
                    dataClass: null,
                    dataCollectableClass: null,
                ),
                'isMixed' => $isMixed,
                'kind' => $kind,
                'dataClass' => null,
                'dataCollectableClass' => null,
            ];
        }

        if ($kind === DataTypeKind::DataObject) {
            return [
                'type' => new NamedType(
                    name: $name,
                    builtIn: $builtIn,
                    acceptedTypes: $acceptedTypes,
                    kind: $kind,
                    dataClass: $name,
                    dataCollectableClass: null,
                ),
                'isMixed' => $isMixed,
                'kind' => $kind,
                'dataClass' => $name,
                'dataCollectableClass' => null,
            ];
        }

        /** @var ?DataCollectionOf $dataCollectionOfAttribute */
        $dataCollectionOfAttribute = $attributes?->first(
            fn (object $attribute) => $attribute instanceof DataCollectionOf
        );

        $dataClass = $dataCollectionOfAttribute?->class;

        $dataClass ??= $classDefinedDataCollectableAnnotation?->dataClass;

        $dataClass ??= $typeable instanceof ReflectionProperty
            ? $this->dataCollectableAnnotationReader->getForProperty($typeable)?->dataClass
            : null;

        if ($dataClass !== null) {
            return [
                'type' => new NamedType(
                    name: $name,
                    builtIn: $builtIn,
                    acceptedTypes: $acceptedTypes,
                    kind: $kind,
                    dataClass: $dataClass,
                    dataCollectableClass: $name,
                ),
                'isMixed' => $isMixed,
                'kind' => $kind,
                'dataClass' => $dataClass,
                'dataCollectableClass' => $name,
            ];
        }

        if (in_array($kind, [DataTypeKind::Array, DataTypeKind::Paginator, DataTypeKind::CursorPaginator, DataTypeKind::Enumerable])) {
            return [
                'type' => new NamedType(
                    name: $name,
                    builtIn: $builtIn,
                    acceptedTypes: $acceptedTypes,
                    kind: DataTypeKind::Default,
                    dataClass: null,
                    dataCollectableClass: null,
                ),
                'isMixed' => $isMixed,
                'kind' => DataTypeKind::Default,
                'dataClass' => null,
                'dataCollectableClass' => null,
            ];
        }

        throw CannotFindDataClass::forTypeable($typeable);
    }

    /**
     * @return array{
     *      type: Type,
     *      isMixed: bool,
     *      lazyType: ?string,
     *      isOptional: bool,
     *      kind: DataTypeKind,
     *      dataClass: ?string,
     *      dataCollectableClass: ?string
     *  }
     *
     */
    protected function inferPropertiesForCombinationType(
        ReflectionUnionType|ReflectionIntersectionType $reflectionType,
        ReflectionClass|string $reflectionClass,
        ReflectionMethod|ReflectionProperty|ReflectionParameter|string $typeable,
        ?Collection $attributes,
        ?DataCollectableAnnotation $classDefinedDataCollectableAnnotation,
    ): array {
        $isMixed = false;
        $isOptional = false;
        $lazyType = null;

        $kind = null;
        $dataClass = null;
        $dataCollectableClass = null;

        $subTypes = [];

        foreach ($reflectionType->getTypes() as $reflectionSubType) {
            if ($reflectionSubType::class === ReflectionUnionType::class || $reflectionSubType::class === ReflectionIntersectionType::class) {
                $properties = $this->inferPropertiesForCombinationType(
                    $reflectionSubType,
                    $reflectionClass,
                    $typeable,
                    $attributes,
                    $classDefinedDataCollectableAnnotation
                );

                $isMixed = $isMixed || $properties['isMixed'];
                $isOptional = $isOptional || $properties['isOptional'];
                $lazyType = $lazyType ?? $properties['lazyType'];

                $kind ??= $properties['kind'];
                $dataClass ??= $properties['dataClass'];
                $dataCollectableClass ??= $properties['dataCollectableClass'];

                $subTypes[] = $properties['type'];

                continue;
            }

            /** @var ReflectionNamedType  $reflectionSubType */

            $name = $reflectionSubType->getName();

            if ($name === Optional::class) {
                $isOptional = true;

                continue;
            }

            if ($name === 'null') {
                continue;
            }

            if (in_array($name, [Lazy::class, DefaultLazy::class, ClosureLazy::class, ConditionalLazy::class, RelationalLazy::class, InertiaLazy::class])) {
                $lazyType = $name;

                continue;
            }

            $properties = $this->inferPropertiesForNamedType(
                $reflectionSubType->getName(),
                $reflectionSubType->isBuiltin(),
                $reflectionClass,
                $typeable,
                $attributes,
                $classDefinedDataCollectableAnnotation
            );

            $isMixed = $isMixed || $properties['isMixed'];

            $kind ??= $properties['kind'];
            $dataClass ??= $properties['dataClass'];
            $dataCollectableClass ??= $properties['dataCollectableClass'];

            $subTypes[] = $properties['type'];
        }

        $type = match (true) {
            count($subTypes) === 0 => throw new Exception('Invalid reflected type'),
            count($subTypes) === 1 => $subTypes[0],
            $reflectionType::class === ReflectionUnionType::class => new UnionType($subTypes),
            $reflectionType::class === ReflectionIntersectionType::class => new IntersectionType($subTypes),
            default => throw new Exception('Invalid reflected type'),
        };

        return [
            'type' => $type,
            'isMixed' => $isMixed,
            'isOptional' => $isOptional,
            'lazyType' => $lazyType,
            'kind' => $kind,
            'dataClass' => $dataClass,
            'dataCollectableClass' => $dataCollectableClass,
        ];
    }
}
