<?php

namespace Spatie\LaravelData\Actions;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;

class ResolveDataObjectFromArrayAction
{
    public function __construct(protected DataConfig $dataConfig)
    {
    }

    public function execute(string $class, array $values): Data
    {
        /** @var \Spatie\LaravelData\Data $data */
        $data = collect($this->dataConfig->getDataProperties($class))
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

        if ($property->castAttribute()) {
            return $this->resolveValueByAttributeCast($property, $value);
        }

        if (empty($property->types())) {
            return $value;
        }

        if ($property->isBuiltIn()) {
            return $value;
        }

        if ($property->isData()) {
            return $this->execute($property->getDataClass(), $value);
        }

        if ($property->isDataCollection()) {
            $items = array_map(
                fn(array $item) => $this->execute($property->getDataClass(), $item),
                $value
            );

            return new DataCollection(
                $property->getDataClass(),
                $items
            );
        }

        if ($cast = $this->resolveGlobalCast($property)) {
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

    private function resolveGlobalCast(DataProperty $property): ?Cast
    {
        if (count($property->types()) !== 1) {
            return null;
        }

        $type = current($property->types());

        return $this->dataConfig->getCastForType($type);
    }
}
