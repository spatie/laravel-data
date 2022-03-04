<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use Spatie\LaravelData\Attributes\MapFrom;

class DataClass
{
    public function __construct(
        public readonly string $name,
        /** @var Collection<string, \Spatie\LaravelData\Support\DataProperty> */
        public readonly Collection $properties,
        /** @var array<string, string> */
        public readonly array $creationMethods,
        public readonly bool $hasAuthorizationMethod,
        public readonly ?string $fromMapperClass,
    ) {
    }

    public static function create(ReflectionClass $class)
    {
        $methods = collect($class->getMethods());

        $attributes = collect($class->getAttributes());

        return new self(
            name: $class->name,
            properties: static::resolveProperties($class),
            creationMethods: static::resolveMagicalMethods($methods),
            hasAuthorizationMethod: static::hasAuthorizationMethod($methods),
            fromMapperClass: $attributes->first(fn (object $attribute) => $attribute instanceof MapFrom)?->from,
        );
    }

    private static function resolveProperties(
        ReflectionClass $class
    ): Collection {
        $defaultValues = static::resolveDefaultValues($class);

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
        ReflectionClass $class
    ): array {
        if (! $class->hasMethod('__construct')) {
            return $class->getDefaultProperties();
        }

        return collect($class->getMethod('__construct')->getParameters())
            ->filter(fn (ReflectionParameter $parameter) => $parameter->isPromoted() && $parameter->isDefaultValueAvailable())
            ->mapWithKeys(fn (ReflectionParameter $parameter) => [
                $parameter->name => $parameter->getDefaultValue(),
            ])
            ->merge($class->getDefaultProperties())
            ->toArray();
    }

    private static function resolveMagicalMethods(
        Collection $methods,
    ): array {
        return $methods
            ->filter(function (ReflectionMethod $method) {
                return $method->isStatic()
                    && $method->isPublic()
                    && (str_starts_with($method->getName(), 'from') || str_starts_with($method->getName(), 'optional'))
                    && $method->getNumberOfParameters() === 1
                    && $method->name !== 'from'
                    && $method->name !== 'optional';
            })
            ->mapWithKeys(function (ReflectionMethod $method) {
                /** @var \ReflectionNamedType|\ReflectionUnionType|null $type */
                $type = current($method->getParameters())->getType();

                if ($type === null) {
                    return [];
                }

                if ($type instanceof ReflectionNamedType) {
                    return [$type->getName() => $method->getName()];
                }

                $entries = [];

                foreach ($type->getTypes() as $subType) {
                    $entries[$subType->getName()] = $method->getName();
                }

                return $entries;
            })->toArray();
    }

    private static function hasAuthorizationMethod(
        Collection $methods
    ): bool {
        return $methods->contains(fn (ReflectionMethod $method) => $method->isStatic()
            && $method->getName() === 'authorize'
            && $method->isPublic());
    }
}
