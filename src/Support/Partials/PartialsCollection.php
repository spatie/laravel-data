<?php

namespace Spatie\LaravelData\Support\Partials;

use SplObjectStorage;

/**
 * @extends SplObjectStorage<Partial, null>
 */
class PartialsCollection extends SplObjectStorage
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
}
