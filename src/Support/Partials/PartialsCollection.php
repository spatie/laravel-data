<?php

namespace Spatie\LaravelData\Support\Partials;

use SplObjectStorage;
use Stringable;

/**
 * @extends SplObjectStorage<Partial, null>
 */
class PartialsCollection extends SplObjectStorage implements Stringable
{
    public static function create(Partial ...$partials): self
    {
        $collection = new self();

        foreach ($partials as $partial) {
            $collection->attach($partial);
        }

        return $collection;
    }

    public function toArray(): array
    {
        $output = [];

        foreach ($this as $partial) {
            $output[] = $partial->toArray();
        }

        return $output;
    }

    public function toSerializedArray(): array
    {
        $output = [];

        foreach ($this as $partial) {
            $output[] = $partial->toSerializedArray();
        }

        return $output;
    }

    public function __toString(): string
    {
        $output = '';

        foreach ($this as $partial) {
            $output .= "  - {$partial}".PHP_EOL;
        }

        return $output;
    }

    public static function fromSerializedArray(array $collection): PartialsCollection
    {
        return self::create(
            ...array_map(
                fn (array $partial) => Partial::fromSerializedArray($partial),
                $collection
            )
        );
    }

    public function manualClone(): PartialsCollection
    {
        $collection = new self();

        foreach ($this as $partial) {
            $collection->attach(clone $partial);
        }

        return $collection;
    }
}
