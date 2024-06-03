<?php

namespace Spatie\LaravelData\Support\Factories;

use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\LaravelData\Contracts\AppendableData;
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
use Spatie\LaravelData\Support\Annotations\DataIterableAnnotationReader;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\LazyDataStructureProperty;

class DataClassFactory
{
    public function __construct(
        protected DataPropertyFactory $propertyFactory,
        protected DataMethodFactory $methodFactory,
        protected DataIterableAnnotationReader $iterableAnnotationReader,
    ) {
    }


    public function build(ReflectionClass $reflectionClass): DataClass
    {
        /** @var class-string<Data> $name */
        $name = $reflectionClass->name;

        $attributes = $this->resolveAttributes($reflectionClass);

        $methods = collect($reflectionClass->getMethods());

        $constructorReflectionMethod = $methods->first(fn (ReflectionMethod $method) => $method->isConstructor());

        $dataIterablePropertyAnnotations = $this->iterableAnnotationReader->getForClass($reflectionClass);

        if ($constructorReflectionMethod) {
            $dataIterablePropertyAnnotations = array_merge(
                $dataIterablePropertyAnnotations,
                $this->iterableAnnotationReader->getForMethod($constructorReflectionMethod)
            );
        }

        $properties = $this->resolveProperties(
            $reflectionClass,
            $constructorReflectionMethod,
            NameMappersResolver::create(ignoredMappers: [ProvidedNameMapper::class])->execute($attributes),
            $dataIterablePropertyAnnotations,
        );

        $responsable = $reflectionClass->implementsInterface(ResponsableData::class);

        $outputMappedProperties = new LazyDataStructureProperty(
            fn () => $properties
                ->map(fn (DataProperty $property) => $property->outputMappedName)
                ->filter()
                ->flip()
                ->toArray()
        );

        $constructor = $constructorReflectionMethod
            ? $this->methodFactory->buildConstructor($constructorReflectionMethod, $reflectionClass, $properties)
            : null;

        return new DataClass(
            name: $reflectionClass->name,
            properties: $properties,
            methods: $this->resolveMethods($reflectionClass),
            constructorMethod: $constructor,
            isReadonly: method_exists($reflectionClass, 'isReadOnly') && $reflectionClass->isReadOnly(),
            isAbstract: $reflectionClass->isAbstract(),
            appendable: $reflectionClass->implementsInterface(AppendableData::class),
            includeable: $reflectionClass->implementsInterface(IncludeableData::class),
            responsable: $responsable,
            transformable: $reflectionClass->implementsInterface(TransformableData::class),
            validateable: $reflectionClass->implementsInterface(ValidateableData::class),
            wrappable: $reflectionClass->implementsInterface(WrappableData::class),
            emptyData: $reflectionClass->implementsInterface(EmptyData::class),
            attributes: $attributes,
            dataIterablePropertyAnnotations: $dataIterablePropertyAnnotations,
            allowedRequestIncludes: new LazyDataStructureProperty(fn (): ?array => $responsable ? $name::allowedRequestIncludes() : null),
            allowedRequestExcludes: new LazyDataStructureProperty(fn (): ?array => $responsable ? $name::allowedRequestExcludes() : null),
            allowedRequestOnly: new LazyDataStructureProperty(fn (): ?array => $responsable ? $name::allowedRequestOnly() : null),
            allowedRequestExcept: new LazyDataStructureProperty(fn (): ?array => $responsable ? $name::allowedRequestExcept() : null),
            outputMappedProperties: $outputMappedProperties,
            transformationFields: static::resolveTransformationFields($properties),
        );
    }

    protected function resolveAttributes(
        ReflectionClass $reflectionClass
    ): Collection {
        $attributes = collect($reflectionClass->getAttributes())
            ->filter(fn (ReflectionAttribute $reflectionAttribute) => class_exists($reflectionAttribute->getName()))
            ->map(fn (ReflectionAttribute $reflectionAttribute) => $reflectionAttribute->newInstance());

        $parent = $reflectionClass->getParentClass();

        if ($parent !== false) {
            $attributes = $attributes->merge(static::resolveAttributes($parent));
        }

        return $attributes;
    }

    protected function resolveMethods(
        ReflectionClass $reflectionClass,
    ): Collection {
        return collect($reflectionClass->getMethods())
            ->filter(fn (ReflectionMethod $reflectionMethod) => str_starts_with($reflectionMethod->name, 'from') || str_starts_with($reflectionMethod->name, 'collect'))
            ->reject(
                fn (ReflectionMethod $reflectionMethod) => in_array($reflectionMethod->name, ['from', 'collect', 'collection'])
                || $reflectionMethod->isStatic() === false
                || $reflectionMethod->isPublic() === false
            )
            ->mapWithKeys(
                fn (ReflectionMethod $reflectionMethod) => [$reflectionMethod->name => $this->methodFactory->build($reflectionMethod, $reflectionClass)],
            );
    }

    protected function resolveProperties(
        ReflectionClass $reflectionClass,
        ?ReflectionMethod $constructorReflectionMethod,
        array $mappers,
        array $dataIterablePropertyAnnotations,
    ): Collection {
        $defaultValues = $this->resolveDefaultValues($reflectionClass, $constructorReflectionMethod);

        return collect($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC))
            ->reject(fn (ReflectionProperty $property) => $property->isStatic())
            ->values()
            ->mapWithKeys(fn (ReflectionProperty $property) => [
                $property->name => $this->propertyFactory->build(
                    $property,
                    $reflectionClass,
                    array_key_exists($property->getName(), $defaultValues),
                    $defaultValues[$property->getName()] ?? null,
                    $mappers['inputNameMapper'],
                    $mappers['outputNameMapper'],
                    $dataIterablePropertyAnnotations[$property->getName()] ?? null,
                ),
            ]);
    }

    protected function resolveDefaultValues(
        ReflectionClass $reflectionClass,
        ?ReflectionMethod $constructorReflectionMethod,
    ): array {
        if (! $constructorReflectionMethod) {
            return $reflectionClass->getDefaultProperties();
        }

        $values = collect($constructorReflectionMethod->getParameters())
            ->filter(fn (ReflectionParameter $parameter) => $parameter->isPromoted() && $parameter->isDefaultValueAvailable())
            ->mapWithKeys(fn (ReflectionParameter $parameter) => [
                $parameter->name => $parameter->getDefaultValue(),
            ])
            ->toArray();

        return array_merge(
            $reflectionClass->getDefaultProperties(),
            $values
        );
    }

    /**
     * @param Collection<string, DataProperty> $properties
     *
     * @return LazyDataStructureProperty<array<string, null|bool>>
     */
    protected function resolveTransformationFields(
        Collection $properties,
    ): LazyDataStructureProperty {
        $closure = fn () => $properties
            ->reject(fn (DataProperty $property): bool => $property->hidden)
            ->map(function (DataProperty $property): null|bool {
                if (
                    $property->type->kind->isDataCollectable()
                    || $property->type->kind->isDataObject()
                    || $property->type->kind === DataTypeKind::Array
                    || $property->type->kind === DataTypeKind::Enumerable
                ) {
                    return true;
                }

                return null;
            })
            ->all();

        return new LazyDataStructureProperty($closure);
    }
}
