<?php

namespace Spatie\LaravelData\Support\Casting;

use ArrayIterator;
use IteratorAggregate;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\DataProperty;
use Traversable;

class GlobalCastsCollection implements IteratorAggregate
{
    /**
     * @param array<string, Cast> $casts
     */
    public function __construct(
        protected array $casts = []
    ) {
    }

    public function add(string $castable, Cast $cast): self
    {
        $this->casts[ltrim($castable, ' \\')] = $cast;

        return $this;
    }

    public function merge(self $casts): self
    {
        $this->casts = array_merge($this->casts, $casts->casts);

        return $this;
    }

    public function findCastForValue(DataProperty $property): ?Cast
    {
        foreach ($property->type->getAcceptedTypes() as $acceptedType => $baseTypes) {
            foreach ([$acceptedType, ...$baseTypes] as $type) {
                if ($cast = $this->casts[$type] ?? null) {
                    return $cast;
                }
            }
        }

        return null;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->casts);
    }
}
