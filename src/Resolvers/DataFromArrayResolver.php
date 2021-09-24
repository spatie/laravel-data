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
            ->mapWithKeys(fn(DataProperty $property) => [
                $property->name() => $this->resolveValue($property, $values[$property->name()] ?? null),
            ])
            ->pipe(fn(Collection $properties) => new $class(...$properties));

        return $data;
    }

    private function resolveValue(DataProperty $property, mixed $value): mixed
    {
        if ($value === null) {
            return $value;
        }

        if (! $this->shouldBeCasted($property, $value)) {
            return $value;
        }

        if ($castAttribute = $property->castAttribute()) {
            return $castAttribute->get()->cast($property, $value);
        }

        if ($property->isBuiltIn()) {
            return $value;
        }

        if ($property->types()->isEmpty()) {
            return $value;
        }

        if ($property->isData()) {
            return $this->execute($property->dataClassName(), $value);
        }

        if ($property->isDataCollection()) {
            $items = array_map(
                fn(array $item) => $this->execute($property->dataClassName(), $item),
                $value
            );

            return new DataCollection(
                $property->dataClassName(),
                $items
            );
        }

        if ($cast = $this->dataConfig->findGlobalCastForProperty($property)) {
            return $cast->cast($property, $value);
        }

        return $value;
    }

    private function shouldBeCasted(DataProperty $property, mixed $value): bool
    {
        $type = gettype($value);

        if ($this->isSimpleType($property, $type)) {
            return false;
        }

        if ($type !== 'object') {
            return true;
        }

        return $property->types()->canBe($type);
    }

    private function isSimpleType(DataProperty $property, string $type): bool
    {
        return ! $property->types()->isEmpty()
            && $property->isBuiltIn()
            && in_array($type, ['bool', 'string', 'int', 'float', 'array']);
    }
}
