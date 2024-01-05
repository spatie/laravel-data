<?php

namespace Spatie\LaravelData\Support\Partials\Segments;

class AllPartialSegment extends PartialSegment
{
    public function __toString(): string
    {
        return '*';
    }
}
