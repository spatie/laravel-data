<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Exceptions\CannotFindDataTypeForProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DataCollectionOf
{
    public function __construct(public string $class)
    {
        if (! is_subclass_of($this->class, Data::class)) {
            throw new CannotFindDataTypeForProperty('Class given does not implement `Data::class`');
        }
    }
}
