<?php

namespace Spatie\LaravelData\Support\Partials;

use SplObjectStorage;
use Stringable;

/**
 * @extends SplObjectStorage<ResolvedPartial, null>
 */
class ResolvedPartialsCollection extends SplObjectStorage implements Stringable
{
    public static function create(ResolvedPartial ...$resolvedPartials): self
    {
        $collection = new self();

        foreach ($resolvedPartials as $resolvedPartial) {
            $collection->attach($resolvedPartial);
        }

        return $collection;
    }

    public function toArray(): array
    {
        $output = [];

        foreach ($this as $resolvedPartial) {
            $output[] = $resolvedPartial->toArray();
        }

        return $output;
    }

    public function __toString(): string
    {
        $output = "- partials:".PHP_EOL;

        foreach ($this as $partial) {
            $output .= "  - {$partial}".PHP_EOL;
        }

        return $output;
    }
}
