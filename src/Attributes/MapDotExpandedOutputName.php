<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class MapDotExpandedOutputName extends MapOutputName
{
    public function __construct(string|int $output)
    {
        parent::__construct($output, expandDotNotation: true);
    }
}
