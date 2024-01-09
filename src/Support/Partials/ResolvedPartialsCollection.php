<?php

namespace Spatie\LaravelData\Support\Partials;

use SplObjectStorage;
use Stringable;

/**
 * @extends SplObjectStorage<ResolvedPartial, null>
 */
class ResolvedPartialsCollection extends SplObjectStorage implements Stringable
{
    public function __toString(): string
    {
        $output = "- excludedPartials:".PHP_EOL;

        foreach ($this as $excludedPartial) {
            $output .= "  - {$excludedPartial}".PHP_EOL;
        }

        return $output;
    }
}
