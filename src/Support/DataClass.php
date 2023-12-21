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
use Spatie\LaravelData\Contracts\IncludeableData;
use Spatie\LaravelData\Contracts\ResponsableData;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Contracts\ValidateableData;
use Spatie\LaravelData\Contracts\WrappableData;
use Spatie\LaravelData\Mappers\ProvidedNameMapper;
use Spatie\LaravelData\Resolvers\NameMappersResolver;
use Spatie\LaravelData\Support\NameMapping\DataClassNameMapping;

/**
 * @property  class-string<DataObject> $name
 * @property  Collection<string, DataProperty> $properties
 * @property  Collection<string, DataMethod> $methods
 * @property  Collection<string, object> $attributes
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
        public readonly Collection $attributes,
        public readonly DataClassNameMapping $outputNameMapping,
    ) {
    }

    public static function create(ReflectionClass $class): self
    {
        $attributes = static::resolveAttributes($class);

        $methods = collect($class->getMethods());

        $constructor = $methods->first(fn (ReflectionMethod $method) => $method->isConstructor());

        $properties = self::resolveProperties(
            $class,
            $constructor,
            NameMappersResolver::create(ignoredMappers: [ProvidedNameMapper::class])->execute($attributes)
        );

        return new self(
            name: $class->name,
            properties: $properties,
            methods: self::resolveMethods($class),
            constructorMethod: DataMethod::createConstructor($constructor, $properties),
            isReadonly: method_exists($class, 'isReadOnly') && $class->isReadOnly(),
            isAbstract: $class->isAbstract(),
            appendable: $class->implementsInterface(AppendableData::class),
            includeable: $class->implementsInterface(IncludeableData::class),
            responsable: $class->implementsInterface(ResponsableData::class),
            transformable: $class->implementsInterface(TransformableData::class),
            validateable: $class->implementsInterface(ValidateableData::class),
            wrappable: $class->implementsInterface(WrappableData::class),
            attributes: $attributes,
            outputNameMapping: self::resolveOutputNameMapping($properties),
        );
    }

    protected static function resolveAttributes(
        ReflectionClass $class
    ): Collection {
        $attributes = collect($class->getAttributes())
            ->filter(fn (ReflectionAttribute $reflectionAttribute) => class_exists($reflectionAttribute->getName()))
            ->map(fn (ReflectionAttribute $reflectionAttribute) => $reflectionAttribute->newInstance());

        $parent = $class->getParentClass();

        if ($parent !== false) {
            $attributes = $attributes->merge(static::resolveAttributes($parent));
        }

        return $attributes;
    }

    protected static function resolveMethods(
        ReflectionClass $reflectionClass,
    ): Collection {
        return collect($reflectionClass->getMethods())
            ->filter(fn (ReflectionMethod $method) => str_starts_with($method->name, 'from'))
            ->mapWithKeys(
                fn (ReflectionMethod $method) => [$method->name => DataMethod::create($method)],
            );
    }

    protected static function resolveProperties(
        ReflectionClass $class,
        ?ReflectionMethod $constructorMethod,
        array $mappers,
    ): Collection {
        $defaultValues = self::resolveDefaultValues($class, $constructorMethod);

        return collect($class->getProperties(ReflectionProperty::IS_PUBLIC))
            ->reject(fn (ReflectionProperty $property) => $property->isStatic())
            ->values()
            ->mapWithKeys(fn (ReflectionProperty $property) => [
                $property->name => DataProperty::create(
                    $property,
                    array_key_exists($property->getName(), $defaultValues),
                    $defaultValues[$property->getName()] ?? null,
                    $mappers['inputNameMapper'],
                    $mappers['outputNameMapper'],
                ),
            ]);
    }

    protected static function resolveDefaultValues(
        ReflectionClass $class,
        ?ReflectionMethod $constructorMethod,
    ): array {
        if (! $constructorMethod) {
            return $class->getDefaultProperties();
        }

        $values = collect($constructorMethod->getParameters())
            ->filter(fn (ReflectionParameter $parameter) => $parameter->isPromoted() && $parameter->isDefaultValueAvailable())
            ->mapWithKeys(fn (ReflectionParameter $parameter) => [
                $parameter->name => $parameter->getDefaultValue(),
            ])
            ->toArray();

        return array_merge(
            $class->getDefaultProperties(),
            $values
        );
    }

    protected static function resolveOutputNameMapping(
        Collection $properties,
    ): DataClassNameMapping {
        $mapped = [];
        $mappedDataObjects = [];

        $properties->each(function (DataProperty $dataProperty) use (&$mapped, &$mappedDataObjects) {
            if ($dataProperty->type->isDataObject || $dataProperty->type->isDataCollectable) {
                $mappedDataObjects[$dataProperty->name] = $dataProperty->type->dataClass;
            }

            if ($dataProperty->outputMappedName === null) {
                return;
            }

            $mapped[$dataProperty->outputMappedName] = $dataProperty->name;
        });

        return new DataClassNameMapping(
            $mapped,
            $mappedDataObjects
        );
    }
}
