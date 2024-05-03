<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Support\Enumerable;
use Spatie\LaravelData\Casts\IterableItemCast;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataProperty;

class CastPropertiesDataPipe implements DataPipe
{
    public function __construct(
        protected DataConfig $dataConfig,
    ) {
    }

    public function handle(
        mixed $payload,
        DataClass $class,
        array $properties,
        CreationContext $creationContext
    ): array {
        foreach ($properties as $name => $value) {
            $dataProperty = $class->properties[$name] ?? null;

            if ($dataProperty === null) {
                continue;
            }

            if ($value === null || $value instanceof Optional || $value instanceof Lazy) {
                continue;
            }

            $properties[$name] = $this->cast($dataProperty, $value, $properties, $creationContext);
        }

        return $properties;
    }

    protected function cast(
        DataProperty $property,
        mixed $value,
        array $properties,
        CreationContext $creationContext
    ): mixed {
        $shouldCast = $this->shouldBeCasted($property, $value);

        if ($shouldCast === false) {
            return $value;
        }

        if ($cast = $property->cast) {
            $casted = $cast->cast($property, $value, $properties, $creationContext);

            if (! $casted instanceof Uncastable) {
                return $casted;
            }
        }

        if ($creationContext->casts) {
            foreach ($creationContext->casts->findCastsForValue($property) as $cast) {
                $casted = $cast->cast($property, $value, $properties, $creationContext);

                if (! $casted instanceof Uncastable) {
                    return $casted;
                }
            }
        }

        foreach ($this->dataConfig->casts->findCastsForValue($property) as $cast) {
            $casted = $cast->cast($property, $value, $properties, $creationContext);

            if (! $casted instanceof Uncastable) {
                return $casted;
            }
        }

        if (
            $property->type->kind->isDataObject()
            || $property->type->kind->isDataCollectable()
        ) {
            try {
                $context = $creationContext->next($property->type->dataClass, $property->name);

                $data = $property->type->kind->isDataObject()
                    ? $context->from($value)
                    : $context->collect($value, $property->type->iterableClass);

                $creationContext->previous();

                return $data;
            } catch (CannotCreateData) {
                return $value;
            }
        }

        if (
            $property->type->kind->isNonDataIteratable()
            && config('data.features.cast_and_transform_iterables', true)
            && is_iterable($value)
        ) {
            return $this->castIterable(
                $property,
                $value,
                $properties,
                $creationContext
            );
        }

        return $value;
    }

    protected function shouldBeCasted(DataProperty $property, mixed $value): bool
    {
        if (gettype($value) !== 'object') {
            return true;
        }

        if ($property->type->kind->isDataCollectable()) {
            return true; // Transform everything to data objects
        }

        return $property->type->acceptsValue($value) === false;
    }

    protected function castIterable(
        DataProperty $property,
        mixed $values,
        array $properties,
        CreationContext $creationContext
    ): iterable {
        if (empty($values)) {
            return $values;
        }

        if ($values instanceof Enumerable) {
            $values = $values->all();
        }

        if (! is_array($values)) {
            return $values;
        }

        if ($property->type->iterableItemType) {
            $values = $this->castIterableItems($property, $values, $properties, $creationContext);
        }

        if ($property->type->kind === DataTypeKind::Array) {
            return $values;
        }

        if ($property->type->kind === DataTypeKind::Enumerable) {
            return new $property->type->iterableClass($values);
        }

        return $values;
    }

    protected function castIterableItems(
        DataProperty $property,
        array $values,
        array $properties,
        CreationContext $creationContext
    ): array {
        /** @var ?IterableItemCast $cast */
        $cast = $this->findCastForIterableItems($property, $values, $properties, $creationContext);

        if ($cast === null) {
            return $values;
        }

        foreach ($values as $key => $value) {
            $values[$key] = $cast->castIterableItem($property, $value, $properties, $creationContext);
        }

        return $values;
    }

    protected function findCastForIterableItems(
        DataProperty $property,
        array $values,
        array $properties,
        CreationContext $creationContext
    ): ?IterableItemCast {
        $firstItem = $values[array_key_first($values)];

        foreach ($creationContext->casts?->findCastsForIterableType($property->type->iterableItemType) ?? [] as $possibleCast) {
            $casted = $possibleCast->castIterableItem($property, $firstItem, $properties, $creationContext);

            if (! $casted instanceof Uncastable) {
                return $possibleCast;
            }
        }

        foreach ($this->dataConfig->casts->findCastsForIterableType($property->type->iterableItemType) as $possibleCast) {
            $casted = $possibleCast->castIterableItem($property, $firstItem, $properties, $creationContext);

            if (! $casted instanceof Uncastable) {
                return $possibleCast;
            }
        }

        return null;
    }
}
