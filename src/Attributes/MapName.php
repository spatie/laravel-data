<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class MapName
{
    public function __construct(public string|int $input, public string|int|null $output = null)
    {
        $this->output ??= $this->input;
    }
}
