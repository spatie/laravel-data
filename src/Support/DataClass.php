<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;

class DataClass
{
    /** @var \Illuminate\Support\Collection */
    private Collection $properties;

    /** @var array<string, string> */
    private array $customFromMethods;

    private bool $hasAuthorizationMethod;

    public function __construct(protected ReflectionClass $class)
    {
        $this->properties = $this->resolveProperties();
    }

    public static function create(ReflectionClass $class): static
    {
        return new self($class);
    }

    public function properties(): Collection
    {
        return $this->properties;
    }

    public function customFromMethods(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->customFromMethods)) {
            return $this->customFromMethods;
        }

        $this->resolveSpecialMethods();

        return $this->customFromMethods;
    }

    public function hasAuthorizationMethod(): bool
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->hasAuthorizationMethod)) {
            return $this->hasAuthorizationMethod;
        }

        $this->resolveSpecialMethods();;

        return $this->hasAuthorizationMethod;
    }

    public function reflection(): ReflectionClass
    {
        return $this->class;
    }

    private function resolveProperties(): Collection
    {
        $properties = [];

        foreach ($this->class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $properties[] = DataProperty::create($property);
        }

        return collect($properties);
    }

    private function resolveSpecialMethods()
    {
        $this->customFromMethods = [];

        $reflectionMethods = $this->class->getMethods(ReflectionMethod::IS_STATIC);

        $this->hasAuthorizationMethod = array_reduce(
            $reflectionMethods,
            fn($hasMethod, ReflectionMethod $method) => $hasMethod || ($method->getName() === 'authorized' && $method->isPublic()),
            false
        );

        $methods = array_filter(
            $reflectionMethods,
            fn (ReflectionMethod $method) => $this->isValidCustomFromMethod($method)
        );

        foreach ($methods as $method) {
            /** @var \ReflectionNamedType|\ReflectionUnionType|null $type */
            $type = current($method->getParameters())->getType();

            if ($type === null) {
                continue;
            }

            if ($type instanceof ReflectionNamedType) {
                $this->customFromMethods[$type->getName()] = $method->getName();

                continue;
            }

            foreach ($type->getTypes() as $subType) {
                $this->customFromMethods[$subType->getName()] = $method->getName();
            }
        }
    }

    private function isValidCustomFromMethod(ReflectionMethod $method): bool
    {
        if (! $method->isPublic()) {
            return false;
        }

        if (! str_starts_with($method->getName(), 'from')) {
            return false;
        }

        if ($method->getNumberOfParameters() !== 1) {
            return false;
        }

        return ! in_array($method->getName(), [
            'from',
            'fromModel',
            'fromArray',
            'fromRequest',
        ]);
    }
}
