<?php

namespace Spatie\LaravelData\Support\Factories;

use Exception;
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
use Spatie\LaravelData\Support\Annotations\CollectionAnnotationReader;
use Spatie\LaravelData\Support\Annotations\DataIterableAnnotationReader;
use Spatie\LaravelData\Support\DataAttributesCollection;
use Spatie\LaravelData\Support\DataPropertyType;
use Spatie\LaravelData\Support\DataType;
use Spatie\LaravelData\Support\Types\IntersectionType;
use Spatie\LaravelData\Support\Types\NamedType;
use Spatie\LaravelData\Support\Types\Storage\AcceptedTypesStorage;
use Spatie\LaravelData\Support\Types\Type;
use Spatie\LaravelData\Support\Types\UnionType;

class DataTypeFactory
{
    public function __construct(
        protected DataIterableAnnotationReader $iterableAnnotationReader,
        protected CollectionAnnotationReader $collectionAnnotationReader,
    ) {
    }

    public function buildProperty(
        ?ReflectionType $reflectionType,
        ReflectionClass|string $class,
        ReflectionProperty|ReflectionParameter|string $typeable,
        ?DataAttributesCollection $attributes = null,
        ?array $classDefinedDataIterableAnnotations = null,
    ): DataPropertyType {
        $properties = $this->infer(
            reflectionType: $reflectionType,
            class: $class,
            typeable: $typeable,
            attributes: $attributes,
            classDefinedDataIterableAnnotations: $classDefinedDataIterableAnnotations,
            inferForProperty: true,
        );

        return new DataPropertyType(
            type: $properties['type'],
            isOptional: $properties['isOptional'],
            isNullable: $reflectionType?->allowsNull() ?? true,
            isMixed: $properties['isMixed'],
            lazyType: $properties['lazyType'],
            kind: $properties['kind'],
            dataClass: $properties['dataClass'],
            dataCollectableClass: $properties['dataCollectableClass'],
            iterableClass: $properties['iterableClass'],
            iterableItemType: $properties['iterableItemType'],
            iterableKeyType: $properties['iterableKeyType'],
        );
    }

    public function build(
        ?ReflectionType $reflectionType,
        ReflectionClass|string $class,
        ReflectionProperty|ReflectionParameter|string $typeable,
    ): DataType {
        $properties = $this->infer(
            reflectionType: $reflectionType,
            class: $class,
            typeable: $typeable,
            attributes: null,
            classDefinedDataIterableAnnotations: null,
            inferForProperty: false,
        );

        return new DataType(
            type: $properties['type'],
            isNullable: $reflectionType?->allowsNull() ?? true,
            isMixed: $properties['isMixed'],
            kind: $properties['kind'],
        );
    }

    public function buildFromString(
        string $type,
        ReflectionClass|string $class,
        bool $isBuiltIn,
        bool $isNullable = false,
    ): DataType {
        $properties = $this->inferPropertiesForNamedType(
            name: $type,
            builtIn: $isBuiltIn,
            class: $class,
            typeable: $type,
            attributes: null,
            classDefinedDataIterableAnnotations: null,
            inferForProperty: false,
        );

        return new DataType(
            type: $properties['type'],
            isNullable: $isNullable,
            isMixed: $properties['isMixed'],
            kind: $properties['kind'],
        );
    }

    /**
     * @return array{
     *      type: Type,
     *      isMixed: bool,
     *      lazyType: ?string,
     *      isOptional: bool,
     *      kind: DataTypeKind,
     *      dataClass: ?string,
     *      dataCollectableClass: ?string,
     *      iterableClass: ?string,
     *      iterableItemType: ?string,
     *      iterableKeyType: ?string
     *  }
     */
    protected function infer(
        ?ReflectionType $reflectionType,
        ReflectionClass|string $class,
        ReflectionMethod|ReflectionProperty|ReflectionParameter|string $typeable,
        ?DataAttributesCollection $attributes,
        ?array $classDefinedDataIterableAnnotations,
        bool $inferForProperty,
    ): array {
        if ($reflectionType === null) {
            return $this->inferPropertiesForNoneType();
        }

        if ($reflectionType instanceof ReflectionNamedType) {
            return $this->inferPropertiesForSingleType(
                $reflectionType,
                $class,
                $typeable,
                $attributes,
                $classDefinedDataIterableAnnotations,
                $inferForProperty,
            );
        }

        if ($reflectionType instanceof ReflectionUnionType || $reflectionType instanceof ReflectionIntersectionType) {
            return $this->inferPropertiesForCombinationType(
                $reflectionType,
                $class,
                $typeable,
                $attributes,
                $classDefinedDataIterableAnnotations,
                $inferForProperty,
            );
        }

        throw new Exception('Invalid reflected type');
    }

    /**
     * @return array{
     *     type: NamedType,
     *     isMixed: bool,
     *     lazyType: ?string,
     *     isOptional: bool,
     *     kind: DataTypeKind,
     *     dataClass: ?string,
     *     dataCollectableClass: ?string,
     *     iterableClass: ?string,
     *     iterableItemType: ?string,
     *     iterableKeyType: ?string
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
            iterableClass: null,
            iterableItemType: null,
            iterableKeyType: null,
        );

        return [
            'type' => $type,
            'isMixed' => true,
            'isOptional' => false,
            'lazyType' => null,
            'kind' => DataTypeKind::Default,
            'dataClass' => null,
            'dataCollectableClass' => null,
            'iterableClass' => null,
            'iterableItemType' => null,
            'iterableKeyType' => null,
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
     *      dataCollectableClass: ?string,
     *      iterableClass: ?string,
     *      iterableItemType: ?string,
     *      iterableKeyType: ?string
     *  }
     */
    protected function inferPropertiesForSingleType(
        ReflectionNamedType $reflectionType,
        ReflectionClass|string $class,
        ReflectionMethod|ReflectionProperty|ReflectionParameter|string $typeable,
        ?DataAttributesCollection $attributes,
        ?array $classDefinedDataIterableAnnotations,
        bool $inferForProperty,
    ): array {
        return [
            ...$this->inferPropertiesForNamedType(
                $reflectionType->getName(),
                $reflectionType->isBuiltin(),
                $class,
                $typeable,
                $attributes,
                $classDefinedDataIterableAnnotations,
                $inferForProperty,
            ),
            'isOptional' => false,
            'lazyType' => null,
        ];
    }

    /**
     * @return array{
     *      type: NamedType|UnionType,
     *      isMixed: bool,
     *      kind: DataTypeKind,
     *      dataClass: ?string,
     *      dataCollectableClass: ?string,
     *      iterableClass: ?string,
     *      iterableItemType: ?string,
     *      iterableKeyType: ?string
     *  }
     */
    protected function inferPropertiesForNamedType(
        string $name,
        bool $builtIn,
        ReflectionClass|string $class,
        ReflectionMethod|ReflectionProperty|ReflectionParameter|string $typeable,
        ?DataAttributesCollection $attributes,
        ?array $classDefinedDataIterableAnnotations,
        bool $inferForProperty,
    ): array {
        if ($name === 'self' || $name === 'static') {
            $name = is_string($class) ? $class : $class->getName();
        }

        $isMixed = $name === 'mixed';

        ['acceptedTypes' => $acceptedTypes, 'kind' => $kind] = AcceptedTypesStorage::getAcceptedTypesAndKind($name);

        if ($kind === DataTypeKind::Default || ($inferForProperty === false && $kind->isDataCollectable())) {
            return [
                'type' => new NamedType(
                    name: $name,
                    builtIn: $builtIn,
                    acceptedTypes: $acceptedTypes,
                    kind: $kind,
                    dataClass: null,
                    dataCollectableClass: null,
                    iterableClass: null,
                    iterableItemType: null,
                    iterableKeyType: null,
                ),
                'isMixed' => $isMixed,
                'kind' => $kind,
                'dataClass' => null,
                'dataCollectableClass' => null,
                'iterableClass' => null,
                'iterableItemType' => null,
                'iterableKeyType' => null,
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
                    iterableClass: null,
                    iterableItemType: null,
                    iterableKeyType: null,
                ),
                'isMixed' => $isMixed,
                'kind' => $kind,
                'dataClass' => $name,
                'dataCollectableClass' => null,
                'iterableClass' => null,
                'iterableItemType' => null,
                'iterableKeyType' => null,
            ];
        }

        $dataCollectionOfAttribute = $attributes?->first(DataCollectionOf::class);

        $isData = false;
        $iterableItemType = null;
        $iterableKeyType = null;

        if ($dataCollectionOfAttribute) {
            $isData = true;
            $iterableItemType = $dataCollectionOfAttribute->class;
        }

        if (
            $iterableItemType === null
            && ! empty($classDefinedDataIterableAnnotations)
        ) {
            if (count($classDefinedDataIterableAnnotations) == 1) {
                $isData = $classDefinedDataIterableAnnotations[0]->isData;
                $iterableItemType = $classDefinedDataIterableAnnotations[0]->type;
                $iterableKeyType = $classDefinedDataIterableAnnotations[0]->keyType;
            } else {
                $isData = array_column($classDefinedDataIterableAnnotations, 'isData');
                $iterableItemType = array_column($classDefinedDataIterableAnnotations, 'type');
                $iterableKeyType = array_column($classDefinedDataIterableAnnotations, 'keyType');
            }
        }

        if (
            $iterableItemType === null
            && $typeable instanceof ReflectionProperty
            && ! empty($annotations = $this->iterableAnnotationReader->getForProperty($typeable))
        ) {
            if (count($annotations) == 1) {
                $isData = $annotations[0]->isData;
                $iterableItemType = $annotations[0]->type;
                $iterableKeyType = $annotations[0]->keyType;
            } else {
                $isData = array_column($annotations, 'isData');
                $iterableItemType = array_column($annotations, 'type');
                $iterableKeyType = array_column($annotations, 'keyType');
            }
        }

        if (
            $iterableItemType === null
            && $typeable instanceof ReflectionProperty
            && class_exists($name)
            && $annotation = $this->collectionAnnotationReader->getForClass($name)
        ) {
            $isData = $annotation->isData;
            $iterableItemType = $annotation->type;
            $iterableKeyType = $annotation->keyType;
        }

        if (is_array($isData)) {
            $types = [];
            foreach ($isData as $i => $iD) {
                $types[] = new NamedType(
                    name: $name,
                    builtIn: $builtIn,
                    acceptedTypes: $acceptedTypes,
                    kind: $iD ? $kind->getDataRelatedEquivalent() : $kind,
                    dataClass: $iD ? $iterableItemType[$i] : null,
                    dataCollectableClass: $iD ? $name : null,
                    iterableClass: $name,
                    iterableItemType: $iterableItemType[$i],
                    iterableKeyType: $iterableKeyType[$i],
                );
            }

            return [
                'type' => new UnionType($types),
                'isMixed' => $isMixed,
                'kind' => $kind,
                'dataClass' => null,
                'dataCollectableClass' => null,
                'iterableClass' => $name,
                'iterableItemType' => join('|', array_unique($iterableItemType)),
                'iterableKeyType' => join('|', array_unique($iterableKeyType)),
            ];
        }

        $kind = $isData
            ? $kind->getDataRelatedEquivalent()
            : $kind;

        if ($iterableItemType !== null || $isData === false) {
            return [
                'type' => new NamedType(
                    name: $name,
                    builtIn: $builtIn,
                    acceptedTypes: $acceptedTypes,
                    kind: $kind,
                    dataClass: $isData ? $iterableItemType : null,
                    dataCollectableClass: $isData ? $name : null,
                    iterableClass: $name,
                    iterableItemType: $iterableItemType,
                    iterableKeyType: $iterableKeyType,
                ),
                'isMixed' => $isMixed,
                'kind' => $kind,
                'dataClass' => $isData ? $iterableItemType : null,
                'dataCollectableClass' => $isData ? $name : null,
                'iterableClass' => $name,
                'iterableItemType' => $iterableItemType,
                'iterableKeyType' => $iterableKeyType,
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
     *      dataCollectableClass: ?string,
     *      iterableClass: ?string,
     *      iterableItemType: ?string,
     *      iterableKeyType: ?string
     *  }
     */
    protected function inferPropertiesForCombinationType(
        ReflectionUnionType|ReflectionIntersectionType $reflectionType,
        ReflectionClass|string $class,
        ReflectionMethod|ReflectionProperty|ReflectionParameter|string $typeable,
        ?DataAttributesCollection $attributes,
        ?array $classDefinedDataIterableAnnotations,
        bool $inferForProperty,
    ): array {
        $isMixed = false;
        $isOptional = false;
        $lazyType = null;

        $kind = null;
        $dataClass = null;
        $dataCollectableClass = null;

        $iterableClass = null;
        $iterableItemType = null;
        $iterableKeyType = null;

        $subTypes = [];

        foreach ($reflectionType->getTypes() as $reflectionSubType) {
            if ($reflectionSubType::class === ReflectionUnionType::class || $reflectionSubType::class === ReflectionIntersectionType::class) {
                $properties = $this->inferPropertiesForCombinationType(
                    $reflectionSubType,
                    $class,
                    $typeable,
                    $attributes,
                    $classDefinedDataIterableAnnotations,
                    $inferForProperty
                );

                $isMixed = $isMixed || $properties['isMixed'];
                $isOptional = $isOptional || $properties['isOptional'];
                $lazyType = $lazyType ?? $properties['lazyType'];

                $kind ??= $properties['kind'];
                $dataClass ??= $properties['dataClass'];
                $dataCollectableClass ??= $properties['dataCollectableClass'];
                $iterableClass ??= $properties['iterableClass'];
                $iterableItemType ??= $properties['iterableItemType'];
                $iterableKeyType ??= $properties['iterableKeyType'];

                $subTypes[] = $properties['type'];

                continue;
            }

            /** @var ReflectionNamedType $reflectionSubType */
            $name = $reflectionSubType->getName();

            if ($name === Optional::class) {
                $isOptional = true;

                continue;
            }

            if ($name === 'null') {
                continue;
            }

            if ($inferForProperty && ($name === Lazy::class || is_subclass_of($name, Lazy::class))) {
                $lazyType = $name;

                continue;
            }

            $properties = $this->inferPropertiesForNamedType(
                $reflectionSubType->getName(),
                $reflectionSubType->isBuiltin(),
                $class,
                $typeable,
                $attributes,
                $classDefinedDataIterableAnnotations,
                $inferForProperty
            );

            $isMixed = $isMixed || $properties['isMixed'];

            $kind ??= $properties['kind'];
            $dataClass ??= $properties['dataClass'];
            $dataCollectableClass ??= $properties['dataCollectableClass'];
            $iterableClass ??= $properties['iterableClass'];
            $iterableItemType ??= $properties['iterableItemType'];
            $iterableKeyType ??= $properties['iterableKeyType'];

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
            'iterableClass' => $iterableClass,
            'iterableItemType' => $iterableItemType,
            'iterableKeyType' => $iterableKeyType,
        ];
    }
}
