<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Exceptions\CannotFindDataClass;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DataCollectionOf
{
    public function __construct(
        /** @var class-string<\Spatie\LaravelData\Data> $class */
        public string $class
    ) {
        if (! is_subclass_of($this->class, Data::class)) {
            throw new CannotFindDataClass('Class given does not implement `Data::class`');
        }
    }
}
