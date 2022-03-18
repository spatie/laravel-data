<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Spatie\LaravelData\Attributes\Concerns\ResolvesNamedMappers;
use Spatie\LaravelData\Mappers\NameMapper;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class MapOutputName
{
    public function __construct(public string|int $output)
    {
    }
}
