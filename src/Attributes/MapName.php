<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Spatie\LaravelData\Mappers\NameMapper;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class MapName
{
    public function __construct(public string|int|NameMapper $input, public string|int|NameMapper|null $output = null)
    {
        $this->output ??= $this->input;
    }
}
