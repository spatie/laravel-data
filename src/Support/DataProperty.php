<?php

namespace Spatie\LaravelData\Support;

use Attribute;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapFrom;
use Spatie\LaravelData\Attributes\Validation\ValidationAttribute;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\CannotFindDataTypeForProperty;
use Spatie\LaravelData\Exceptions\InvalidDataPropertyType;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Transformers\Transformer;
use Spatie\LaravelData\Undefined;
use TypeError;

class DataProperty
{
    public function __construct(
        public readonly string $name,
        public readonly string $className,
        public readonly DataPropertyTypes $types,
        public readonly bool $validate,
        public readonly bool $lazy,
        public readonly bool $nullable,
        public readonly bool $undefinable,
        public readonly bool $promoted,
        public readonly bool $isDataObject,
        public readonly bool $isDataCollection,
        public readonly bool $hasDefaultValue,
        public readonly mixed $defaultValue,
        public readonly array $validationAttributes,
        public readonly ?Cast $cast,
        public readonly ?Transformer $transformer,
        public readonly ?MapFrom $mapFrom,
        /** @var class-string<\Spatie\LaravelData\Data> */
        public readonly ?string $dataClass,
        public readonly array $attributes,
    ) {
        $this->ensurePropertyIsValid();
    }

    public static function create(
        ReflectionProperty $property,
        bool $hasDefaultValue = false,
        mixed $defaultValue = null
    ) {
        $type = $property->getType();

        $attributes = collect($property->getAttributes())->map(
            fn(ReflectionAttribute $reflectionAttribute) => $reflectionAttribute->newInstance()
        );

        $parameters = [
            'name' => $property->name,
            'className' => $property->class,
            'validate' => ! $attributes->contains(fn(object $attribute) => $attribute instanceof WithoutValidation),
            'promoted' => $property->isPromoted(),
            'hasDefaultValue' => $hasDefaultValue,
            'defaultValue' => $defaultValue,
            'validationAttributes' => $attributes->filter(fn(object $attribute) => $attribute instanceof ValidationAttribute)->all(),
            'cast' => $attributes->first(fn(object $attribute) => $attribute instanceof WithCast)?->get(),
            'transformer' => $attributes->first(fn(object $attribute) => $attribute instanceof WithTransformer)?->get(),
            'mapFrom' => $attributes->first(fn(object $attribute) => $attribute instanceof MapFrom),
            'attributes' => $attributes->all(),
        ];

        $specificParameters = match (true) {
            $type === null => static::processNoType(),
            $type instanceof ReflectionNamedType => static::processNamedType($property, $type, $attributes),
            $type instanceof ReflectionUnionType, $type instanceof ReflectionIntersectionType => self::processListType($property, $type, $attributes),
            default => throw new TypeError(),
        };

        return new self(...array_merge($parameters, $specificParameters));
    }

    private static function processNoType(): array
    {
        return [
            'types' => new DataPropertyTypes(),
            'lazy' => false,
            'nullable' => true,
            'undefinable' => false,
            'isDataObject' => false,
            'isDataCollection' => false,
            'dataClass' => null,
        ];
    }

    private static function processNamedType(
        ReflectionProperty $property,
        ReflectionNamedType $type,
        Collection $attributes,
    ): array {
        $name = $type->getName();

        if (is_a($name, Lazy::class, true)) {
            throw InvalidDataPropertyType::onlyLazy($property);
        }

        if (is_a($name, Undefined::class, true)) {
            throw InvalidDataPropertyType::onlyUndefined($property);
        }

        $isDataObject = is_a($name, Data::class, true);
        $isDataCollection = is_a($name, DataCollection::class, true);

        return [
            'types' => new DataPropertyTypes([$name]),
            'lazy' => false,
            'nullable' => $type->allowsNull(),
            'undefinable' => false,
            'isDataObject' => $isDataObject,
            'isDataCollection' => $isDataCollection,
            'dataClass' => match (true) {
                $isDataObject => $name,
                $isDataCollection => static::resolveDataCollectionClass($property, $attributes),
                default => null
            },
        ];
    }

    private static function processListType(
        ReflectionProperty $property,
        ReflectionUnionType|ReflectionIntersectionType $types,
        Collection $attributes,
    ): array {
        $parameters = [
            'types' => new DataPropertyTypes(),
            'lazy' => false,
            'nullable' => false,
            'undefinable' => false,
            'isDataObject' => false,
            'isDataCollection' => false,
            'dataClass' => null,
        ];

        foreach ($types->getTypes() as $childType) {
            $name = $childType->getName();

            if ($name === 'null') {
                $parameters['nullable'] = true;

                continue;
            }

            if ($name === Undefined::class) {
                $parameters['undefinable'] = true;

                continue;
            }

            if ($name === Lazy::class) {
                $parameters['lazy'] = true;

                continue;
            }

            if (is_a($name, Data::class, true)) {
                $parameters['isDataObject'] = true;
                $parameters['types']->add($name);
                $parameters['dataClass'] = $name;

                continue;
            }

            if (is_a($name, DataCollection::class, true)) {
                $parameters['isDataCollection'] = true;
                $parameters['types']->add($name);
                $parameters['dataClass'] = static::resolveDataCollectionClass($property, $attributes);

                continue;
            }

            $parameters['types']->add($name);
        }

        return $parameters;
    }

    private static function resolveDataCollectionClass(
        ReflectionProperty $property,
        Collection $attributes,
    ): string {
        if ($dataCollectionOf = $attributes->first(fn(object $attribute) => $attribute instanceof DataCollectionOf)) {
            return $dataCollectionOf->class;
        }

        $class = (new DataCollectionAnnotationReader())->getClass($property);

        if ($class === null) {
            throw CannotFindDataTypeForProperty::wrongDataCollectionAnnotation(
                $property->class,
                $property->name
            );
        }

        return $class;
    }

    private function ensurePropertyIsValid()
    {
        if ($this->isDataObject && $this->types->count() > 1) {
            throw InvalidDataPropertyType::unionWithData($this);
        }

        if ($this->isDataCollection && $this->types->count() > 1) {
            throw InvalidDataPropertyType::unionWithDataCollection($this);
        }
    }
}
