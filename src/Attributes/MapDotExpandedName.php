<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class MapDotExpandedName extends MapName
{
    public function __construct(string|int $input, string|int|null $output = null)
    {
        parent::__construct($input, $output, expandDotNotation: true);
    }
}
