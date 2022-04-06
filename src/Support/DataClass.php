<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\LaravelData\Mappers\ProvidedNameMapper;
use Spatie\LaravelData\Resolvers\NameMappersResolver;

class DataClass
{
    public function __construct(
        public readonly string $name,
        /** @var Collection<\Spatie\LaravelData\Support\DataProperty> */
        public readonly Collection $properties,
        /** @var Collection<string, \Spatie\LaravelData\Support\DataMethod> */
        public readonly Collection $methods,
        public readonly ?DataMethod $constructorMethod,
    ) {
    }

    public static function create(ReflectionClass $class)
    {
        $attributes = collect($class->getAttributes())->map(
            fn (ReflectionAttribute $reflectionAttribute) => $reflectionAttribute->newInstance()
        );

        $methods = collect($class->getMethods());

        $constructor = $methods->first(fn (ReflectionMethod $method) => $method->isConstructor());

        $properties = static::resolveProperties(
            $class,
            $constructor,
            NameMappersResolver::create(ignoredMappers: [ProvidedNameMapper::class])->execute($attributes)
        );

        return new self(
            name: $class->name,
            properties: $properties,
            methods: static::resolveMethods($class),
            constructorMethod: DataMethod::createConstructor($constructor, $properties),
        );
    }

    private static function resolveMethods(
        ReflectionClass $reflectionClass,
    ): Collection {
        return collect($reflectionClass->getMethods())
            ->filter(fn (ReflectionMethod $method) => str_starts_with($method->name, 'from'))
            ->mapWithKeys(
                fn (ReflectionMethod $method) => [$method->name => DataMethod::create($method)],
            );
    }

    private static function resolveProperties(
        ReflectionClass $class,
        ?ReflectionMethod $constructorMethod,
        array $mappers,
    ): Collection {
        $defaultValues = static::resolveDefaultValues($class, $constructorMethod);

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


    private static function resolveDefaultValues(
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
