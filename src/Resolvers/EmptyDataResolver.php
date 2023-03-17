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

        $payload = [];

        foreach ($dataClass->properties as $property) {
            $name = $property->outputMappedName ?? $property->name;

            if ($property->hasDefaultValue) {
                $payload[$name] = $property->defaultValue;
            } else {
                $payload[$name] = $extra[$property->name] ?? $this->getValueForProperty($property);
            }
        }

        return $payload;
    }

    protected function getValueForProperty(DataProperty $property): mixed
    {
        if ($property->type->isMixed) {
            return null;
        }

        if ($property->type->count() > 1) {
            throw DataPropertyCanOnlyHaveOneType::create($property);
        }

        if ($property->type->acceptsType('array')) {
            return [];
        }

        if ($property->type->isDataObject) {
            return $property->type->dataClass::empty();
        }

        if ($property->type->isDataCollectable) {
            return [];
        }

        if ($property->type->findAcceptedTypeForBaseType(Traversable::class) !== null) {
            return [];
        }

        return null;
    }
}
