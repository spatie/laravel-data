<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class CollectionOf
{
    public function __construct(
        public string $target
    ){}
}
