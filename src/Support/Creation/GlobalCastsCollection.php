<?php

namespace Spatie\LaravelData\Support\Creation;

use ArrayIterator;
use Generator;
use IteratorAggregate;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\IterableItemCast;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Types\Storage\AcceptedTypesStorage;
use Traversable;

class GlobalCastsCollection implements IteratorAggregate
{
    /**
     * @param array<string, Cast> $casts
     * @param array<string, IterableItemCast> $iterableItemCasts
     */
    public function __construct(
        protected array $casts = [],
        protected array $iterableItemCasts = []
    ) {
    }

    public function add(string $castable, Cast|IterableItemCast $cast): self
    {
        $castable = ltrim($castable, ' \\');

        if($cast instanceof Cast) {
            $this->casts[$castable] = $cast;
        }

        if($cast instanceof IterableItemCast) {
            $this->iterableItemCasts[$castable] = $cast;
        }

        return $this;
    }

    public function merge(self $casts): self
    {
        $this->casts = array_merge($this->casts, $casts->casts);
        $this->iterableItemCasts = array_merge($this->iterableItemCasts, $casts->iterableItemCasts);

        return $this;
    }

    /**
     * @deprecated  use `findCastsForValue` instead
     */
    public function findCastForValue(DataProperty $property): ?Cast
    {
        foreach ($this->findCastsForValue($property) as $cast) {
            return $cast;
        }

        return null;
    }

    public function findCastsForValue(DataProperty $property): Generator
    {
        foreach ($property->type->getAcceptedTypes() as $acceptedType => $baseTypes) {
            foreach ([$acceptedType, ...$baseTypes] as $type) {
                if ($cast = $this->casts[$type] ?? null) {
                    yield $cast;
                }
            }
        }
    }

    public function findCastsForIterableType(string $type): Generator
    {
        if ($cast = $this->iterableItemCasts[$type] ?? null) {
            yield $cast;
        }

        foreach (AcceptedTypesStorage::getAcceptedTypes($type) as $acceptedType) {
            if ($cast = $this->iterableItemCasts[$acceptedType] ?? null) {
                yield $cast;
            }
        }
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator(array_unique(array_merge(array_keys($this->casts), array_keys($this->iterableItemCasts))));
    }
}
