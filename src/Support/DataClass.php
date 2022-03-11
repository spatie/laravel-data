<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Collection;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Spatie\LaravelData\Attributes\MapFrom;

class DataClass
{
    public function __construct(
        public readonly string $name,
        /** @var Collection<\Spatie\LaravelData\Support\DataProperty> */
        public readonly Collection $properties,
        /** @var Collection<string, \Spatie\LaravelData\Support\DataMethod> */
        public readonly Collection $methods,
        public readonly ?MapFrom $mapFrom,
    ) {
    }

    public static function create(ReflectionClass $class)
    {
        $attributes = collect($class->getAttributes())->map(
            fn (ReflectionAttribute $reflectionAttribute) => $reflectionAttribute->newInstance()
        );

        $methods = static::resolveMethods($class);

        return new self(
            name: $class->name,
            properties: static::resolveProperties($class, $methods->get('__construct')),
            methods: $methods,
            mapFrom: $attributes->first(fn (object $attribute) => $attribute instanceof MapFrom),
        );
    }

    private static function resolveMethods(
        ReflectionClass $reflectionClass,
    ): Collection {
        return collect($reflectionClass->getMethods())
            ->filter(
                fn (ReflectionMethod $method) => $method->name === '__construct' || str_starts_with($method->name, 'from')
            )
            ->mapWithKeys(
                fn (ReflectionMethod $method) => [$method->name => DataMethod::create($method)],
            );
    }

    private static function resolveProperties(
        ReflectionClass $class,
        ?DataMethod $constructorMethod,
    ): Collection {
        $defaultValues = static::resolveDefaultValues($class, $constructorMethod);

        return collect($class->getProperties(ReflectionProperty::IS_PUBLIC))
            ->reject(fn (ReflectionProperty $property) => $property->isStatic())
            ->map(fn (ReflectionProperty $property) => DataProperty::create(
                $property,
                array_key_exists($property->getName(), $defaultValues),
                $defaultValues[$property->getName()] ?? null,
            ))
            ->values();
    }


    private static function resolveDefaultValues(
        ReflectionClass $class,
        ?DataMethod $constructorMethod,
    ): array {
        if (! $constructorMethod) {
            return $class->getDefaultProperties();
        }

        $values = $constructorMethod
            ->parameters
            ->filter(fn (DataParameter $parameter) => $parameter->isPromoted && $parameter->hasDefaultValue)
            ->mapWithKeys(fn (DataParameter $parameter) => [
                $parameter->name => $parameter->defaultValue,
            ])
            ->toArray();

        return array_merge(
            $class->getDefaultProperties(),
            $values
        );
    }
}
