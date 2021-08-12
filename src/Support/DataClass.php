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
    private array $creationMethods;

    /** @var array<string, string> */
    private array $optionalCreationMethods;

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

    public function creationMethods(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->creationMethods)) {
            return $this->creationMethods;
        }

        $this->resolveMagicalMethods();

        return $this->creationMethods;
    }

    public function optionalCreationMethods(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->optionalCreationMethods)) {
            return $this->optionalCreationMethods;
        }

        $this->resolveMagicalMethods();

        return $this->optionalCreationMethods;
    }

    public function hasAuthorizationMethod(): bool
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (isset($this->hasAuthorizationMethod)) {
            return $this->hasAuthorizationMethod;
        }

        $this->resolveMagicalMethods();

        return $this->hasAuthorizationMethod;
    }

    public function reflection(): ReflectionClass
    {
        return $this->class;
    }

    private function resolveProperties(): Collection
    {
        return collect($this->class->getProperties(ReflectionProperty::IS_PUBLIC))
            ->reject(fn (ReflectionProperty $property) => $property->isStatic())
            ->map(fn (ReflectionProperty $property) => DataProperty::create($property))
            ->values();
    }

    private function resolveMagicalMethods()
    {
        $this->creationMethods = [];

        $methods = collect($this->class->getMethods(ReflectionMethod::IS_STATIC));

        $this->hasAuthorizationMethod = $methods->contains(
            fn (ReflectionMethod $method) => $method->getName() === 'authorized' && $method->isPublic()
        );

        [$creationMethods, $optionalCreationMethods] = $methods
            ->filter(function (ReflectionMethod $method) {
                return $method->isPublic()
                    && (str_starts_with($method->getName(), 'from') || str_starts_with($method->getName(), 'optional'))
                    && $method->getNumberOfParameters() === 1
                    && $method->name !== 'from'
                    && $method->name !== 'optional';
            })
            ->partition(fn (ReflectionMethod $method) => str_starts_with($method->getName(), 'from'));

        $this->creationMethods = $this->extractTypesFromCreationalMethods($creationMethods);
        $this->optionalCreationMethods = $this->extractTypesFromCreationalMethods($optionalCreationMethods);
    }

    private function extractTypesFromCreationalMethods(Collection $methods): array
    {
        return $methods->mapWithKeys(function (ReflectionMethod $method) {
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
}
