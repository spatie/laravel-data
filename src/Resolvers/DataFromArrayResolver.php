<?php

namespace Spatie\LaravelData\Resolvers;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;

class DataFromArrayResolver
{
    public function __construct(protected DataConfig $dataConfig)
    {
    }

    public function execute(string $class, array $values): Data
    {
        /** @var \Spatie\LaravelData\Data $data */
        $data = $this->dataConfig->getDataClass($class)
            ->properties()
            ->mapWithKeys(fn (DataProperty $property) => [
                $property->name() => $this->resolveValue($property, $values[$property->name()] ?? null),
            ])
            ->pipe(fn (Collection $properties) => new $class(...$properties));

        return $data;
    }

    private function resolveValue(DataProperty $property, mixed $value): mixed
    {
        if ($value === null) {
            return $value;
        }

        if ($property->castAttribute()) {
            return $this->resolveValueByAttributeCast($property, $value);
        }

        if ($property->isBuiltIn()) {
            return $value;
        }

        if (empty($property->types())) {
            return $value;
        }

        if ($property->isData()) {
            return $this->execute($property->getDataClassName(), $value);
        }

        if ($property->isDataCollection()) {
            $items = array_map(
                fn (array $item) => $this->execute($property->getDataClassName(), $item),
                $value
            );

            return new DataCollection(
                $property->getDataClassName(),
                $items
            );
        }

        if ($cast = $this->dataConfig->findGlobalCastForProperty($property)) {
            return $cast->cast($property, $value);
        }

        return $value;
    }

    private function resolveValueByAttributeCast(
        DataProperty $property,
        mixed $value
    ): mixed {
        return $property->castAttribute()->get()->cast($property, $value);
    }
}
