<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Undefined;

class CastPropertiesPipe extends DataPipe
{
    public function __construct(protected DataConfig $dataConfig)
    {
    }

    public function execute(mixed $value, Collection $payload, DataClass $class): Collection
    {
        return $class->properties()->mapWithKeys(function (DataProperty $property) use ($payload) {
            $value = $this->resolveValue($payload, $property);

            return [$property->name() => $this->castValue($property, $value)];
        });
    }

    private function resolveValue(Collection $payload, DataProperty $property): mixed
    {
        if (! $payload->has($property->name()) && $property->hasDefaultValue()) {
            return $property->defaultValue();
        }

        if (! $payload->has($property->name()) && $property->isUndefinable()) {
            return Undefined::create();
        }

        return $payload->get($property->name());
    }

    private function castValue(DataProperty $property, mixed $value): mixed
    {
        if ($value === null) {
            return $value;
        }

        if ($value instanceof Undefined) {
            return $value;
        }

        if ($value instanceof Lazy) {
            return $value;
        }

        $shouldCast = $this->shouldBeCasted($property, $value);

        if ($shouldCast && $castAttribute = $property->castAttribute()) {
            return $castAttribute->get()->cast($property, $value);
        }

        if ($shouldCast && $cast = $this->dataConfig->findGlobalCastForProperty($property)) {
            return $cast->cast($property, $value);
        }

        if ($property->isData()) {
            return $property->dataClassName()::from($value);
        }

        if ($property->isDataCollection() && $value instanceof DataCollection) {
            return $value;
        }

        if ($property->isDataCollection()) {
            $items = array_map(
                fn ($item) => $property->dataClassName()::from($item),
                $value
            );

            return new DataCollection(
                $property->dataClassName(),
                $items
            );
        }

        return $value;
    }

    private function shouldBeCasted(DataProperty $property, mixed $value): bool
    {
        $type = gettype($value);

        if ($type !== 'object') {
            return true;
        }

        return $property->types()->canBe($type);
    }
}
