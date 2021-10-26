<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;

class DataClass
{
    protected Collection $properties;

    /** @var array<string, string> */
    protected array $creationMethods;

    protected bool $hasAuthorizationMethod;

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

        $this->creationMethods = $methods
            ->filter(function (ReflectionMethod $method) {
                return $method->isPublic()
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
}
