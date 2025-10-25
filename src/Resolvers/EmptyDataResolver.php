<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Arr;
use Spatie\LaravelData\Concerns\EmptyData;
use Spatie\LaravelData\Exceptions\DataPropertyCanOnlyHaveOneType;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Types\CombinationType;
use Traversable;

class EmptyDataResolver
{
    public function __construct(protected DataConfig $dataConfig)
    {
    }

    public function execute(string $class, array $extra = [], mixed $defaultReturnValue = null): array
    {
        $dataClass = $this->dataConfig->getDataClass($class);

        $payload = [];

        foreach ($dataClass->properties as $property) {
            $name = $property->outputMappedName ?? $property->name;

            $value = $property->hasDefaultValue
                ? $property->defaultValue
                : ($extra[$property->name] ?? $this->getValueForProperty($property, $defaultReturnValue));

            if ($property->expandDotNotation) {
                Arr::set($payload, $name, $value);
            } else {
                $payload[$name] = $value;
            }
        }

        return $payload;
    }

    protected function getValueForProperty(DataProperty $property, mixed $defaultReturnValue = null): mixed
    {
        $propertyType = $property->type;

        if ($propertyType->isMixed) {
            return $defaultReturnValue;
        }

        if ($propertyType->type instanceof CombinationType && count($propertyType->type->types) > 1) {
            throw DataPropertyCanOnlyHaveOneType::create($property);
        }

        if ($propertyType->type->acceptsType('array')) {
            return [];
        }

        if ($propertyType->kind->isDataObject()
            && $this->dataConfig->getDataClass($propertyType->dataClass)->emptyData
        ) {
            /** @var class-string<EmptyData> $dataClass */
            $dataClass = $propertyType->dataClass;

            return $dataClass::empty();
        }

        if ($propertyType->kind->isDataCollectable()) {
            return [];
        }

        if ($propertyType->type->findAcceptedTypeForBaseType(Traversable::class) !== null) {
            return [];
        }

        return $defaultReturnValue;
    }
}
