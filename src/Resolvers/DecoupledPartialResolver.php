<?php

namespace Spatie\LaravelData\Resolvers;

use Spatie\LaravelData\Support\Partials\Partial;
use Spatie\LaravelData\Support\Partials\Segments\AllPartialSegment;

class DecoupledPartialResolver
{
    public function execute(Partial $partial): ?Partial
    {
        $decoupledSegments = $this->resolveDecoupledSegments($partial);

        if (empty($decoupledSegments)) {
            return null;
        }

        $clone = clone $partial;

        $clone->segments = $decoupledSegments;
        $clone->segmentCount = count($clone->segments);
        $clone->pointer = 0;

        return $clone;
    }

    protected function resolveDecoupledSegments(
        Partial $partial
    ): array {
        if ($partial->endsInAll && $partial->pointer >= $partial->segmentCount) {
            return [new AllPartialSegment()];
        }

        return array_slice($partial->segments, $partial->pointer);
    }
}
