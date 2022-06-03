<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Spatie\LaravelData\DataObject;
use Spatie\LaravelData\Exceptions\CannotFindDataClass;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DataCollectionOf
{
    public function __construct(
        /** @var class-string<DataObject> $class */
        public string $class
    ) {
        if (! is_subclass_of($this->class, DataObject::class)) {
            throw new CannotFindDataClass('Class given does not implement `DataObject::class`');
        }
    }
}
