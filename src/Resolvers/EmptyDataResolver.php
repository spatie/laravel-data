<?php

namespace Spatie\LaravelData\Resolvers;

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

        return $dataClass->properties->reduce(function (array $payload, DataProperty $property) use ($extra) {
            if ($property->hasDefaultValue) {
                $payload[$property->name] = $property->defaultValue;
            } else {
                $payload[$property->name] = $extra[$property->name] ?? $this->getValueForProperty($property);
            }

            return $payload;
        }, []);
    }

    private function getValueForProperty(DataProperty $property): mixed
    {
        if ($property->types->isEmpty()) {
            return null;
        }

        if ($property->types->count() > 1) {
            throw DataPropertyCanOnlyHaveOneType::create($property);
        }

        $type = $property->types->first();

        if ($type === 'array') {
            return [];
        }

        if ($property->isDataObject) {
            /** @var \Spatie\LaravelData\Data $type */
            return $type::empty();
        }

        if ($property->isDataCollection) {
            return [];
        }

        if (is_a($type, Traversable::class, true)) {
            return [];
        }

        return null;
    }
}
