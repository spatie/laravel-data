<?php

namespace Spatie\LaravelData\Support;

use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;

class DataClass
{
    /** @var \Illuminate\Support\Collection<\Spatie\LaravelData\Support\DataProperty> */
    private Collection $properties;

    /** @var array<string, string> */
    private array $customFromMethods;

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
        if (isset($this->customFromMethods)) {
            return $this->customFromMethods;
        }

        $this->customFromMethods = [];

        $methods = array_filter(
            $this->class->getMethods(ReflectionMethod::IS_STATIC),
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

        return $this->customFromMethods;
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
