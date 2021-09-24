<?php

namespace Spatie\LaravelData\Resolvers;

use ReflectionClass;
use ReflectionParameter;
use Spatie\LaravelData\Exceptions\DataPropertyCanOnlyHaveOneType;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Traversable;

class EmptyDataResolver
{
    public function __construct(protected DataConfig $dataConfig)
    {
    }

    public function execute(string $class, array $extra = []): array
    {
        $dataClass = $this->dataConfig->getDataClass($class);

        $defaults = $this->resolveDefaults($dataClass->reflection(), $extra);

        return $dataClass->properties()->reduce(function (array $payload, DataProperty $property) use ($defaults) {
            $payload[$property->name()] = $defaults[$property->name()] ?? $this->getValueForProperty($property);

            return $payload;
        }, []);
    }

    private function resolveDefaults(ReflectionClass $reflection, array $extra): array
    {
        $defaultConstructorProperties = [];

        if ($reflection->hasMethod('__construct')) {
            $defaultConstructorProperties = collect($reflection->getMethod('__construct')->getParameters())
                ->filter(fn (ReflectionParameter $parameter) => $parameter->isPromoted() && $parameter->isDefaultValueAvailable())
                ->mapWithKeys(fn (ReflectionParameter $parameter) => [
                    $parameter->name => $parameter->getDefaultValue(),
                ])
                ->toArray();
        }

        return array_merge(
            $reflection->getDefaultProperties(),
            $defaultConstructorProperties,
            $extra
        );
    }

    private function getValueForProperty(DataProperty $property): mixed
    {
        if ($property->types()->isEmpty()) {
            return null;
        }

        if ($property->types()->count() > 1) {
            throw DataPropertyCanOnlyHaveOneType::create($property);
        }

        $type = $property->types()->first();

        if ($type === 'array') {
            return [];
        }

        if ($property->isBuiltIn()) {
            return null;
        }

        if ($property->isData()) {
            /** @var \Spatie\LaravelData\Data $type */
            return $type::empty();
        }

        if ($property->isDataCollection()) {
            return [];
        }

        if (is_a($type, Traversable::class, true)) {
            return [];
        }

        return null;
    }
}
