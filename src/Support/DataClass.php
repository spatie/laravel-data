<?php

namespace Spatie\LaravelData\Support;

use Hoa\Compiler\Llk\TreeNode;
use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\LaravelData\Contracts\AppendableData;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\DataObject;
use Spatie\LaravelData\Contracts\DefaultableData;
use Spatie\LaravelData\Contracts\IncludeableData;
use Spatie\LaravelData\Contracts\ResponsableData;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Contracts\ValidateableData;
use Spatie\LaravelData\Contracts\WrappableData;
use Spatie\LaravelData\Mappers\ProvidedNameMapper;
use Spatie\LaravelData\Resolvers\AllowedRequestPartialsResolver;
use Spatie\LaravelData\Resolvers\NameMappersResolver;
use Spatie\LaravelData\Support\Annotations\DataCollectableAnnotationReader;
use Spatie\LaravelData\Support\NameMapping\DataClassNameMapping;

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
        public readonly bool $defaultable,
        public readonly bool $wrappable,
        public readonly Collection $attributes,
        public readonly array $dataCollectablePropertyAnnotations,
        public readonly ?array $allowedRequestIncludes,
        public readonly ?array $allowedRequestExcludes,
        public readonly ?array $allowedRequestOnly,
        public readonly ?array $allowedRequestExcept,
        public readonly array $outputMappedProperties,
    ) {
    }

    public static function create(ReflectionClass $class): self
    {
        /** @var class-string<BaseData> $name */
        $name = $class->name;

        $attributes = static::resolveAttributes($class);

        $methods = collect($class->getMethods());

        $constructor = $methods->first(fn (ReflectionMethod $method) => $method->isConstructor());

        $dataCollectablePropertyAnnotations = DataCollectableAnnotationReader::create()->getForClass($class);

        if ($constructor) {
            $dataCollectablePropertyAnnotations = array_merge(
                $dataCollectablePropertyAnnotations,
                DataCollectableAnnotationReader::create()->getForMethod($constructor)
            );
        }

        $properties = self::resolveProperties(
            $class,
            $constructor,
            NameMappersResolver::create(ignoredMappers: [ProvidedNameMapper::class])->execute($attributes),
            $dataCollectablePropertyAnnotations,
        );

        $responsable = $class->implementsInterface(ResponsableData::class);

        $outputMappedProperties = $properties
            ->map(fn (DataProperty $property) => $property->outputMappedName)
            ->filter()
            ->flip()
            ->toArray();

        return new self(
            name: $class->name,
            properties: $properties,
            methods: self::resolveMethods($class),
            constructorMethod: DataMethod::createConstructor($constructor, $properties),
            isReadonly: method_exists($class, 'isReadOnly') && $class->isReadOnly(),
            isAbstract: $class->isAbstract(),
            appendable: $class->implementsInterface(AppendableData::class),
            includeable: $class->implementsInterface(IncludeableData::class),
            responsable: $responsable,
            transformable: $class->implementsInterface(TransformableData::class),
            validateable: $class->implementsInterface(ValidateableData::class),
            defaultable: $class->implementsInterface(DefaultableData::class),
            wrappable: $class->implementsInterface(WrappableData::class),
            attributes: $attributes,
            dataCollectablePropertyAnnotations: $dataCollectablePropertyAnnotations,
            allowedRequestIncludes: $responsable ? $name::allowedRequestIncludes() : null,
            allowedRequestExcludes: $responsable ? $name::allowedRequestExcludes() : null,
            allowedRequestOnly: $responsable ? $name::allowedRequestOnly() : null,
            allowedRequestExcept: $responsable ? $name::allowedRequestExcept() : null,
            outputMappedProperties: $outputMappedProperties,
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
            ->filter(fn (ReflectionMethod $method) => str_starts_with($method->name, 'from') || str_starts_with($method->name, 'collect'))
            ->reject(fn (ReflectionMethod $method) => in_array($method->name, ['from', 'collect', 'collection']))
            ->mapWithKeys(
                fn (ReflectionMethod $method) => [$method->name => DataMethod::create($method)],
            );
    }

    protected static function resolveProperties(
        ReflectionClass $class,
        ?ReflectionMethod $constructorMethod,
        array $mappers,
        array $dataCollectablePropertyAnnotations,
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
                    $dataCollectablePropertyAnnotations[$property->getName()] ?? null,
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
}
