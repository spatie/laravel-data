<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Exceptions\CannotFindDataClass;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DataCollectionOf
{
    public function __construct(
        /** @var class-string<\Spatie\LaravelData\Contracts\BaseData> $class */
        public string $class
    ) {
        if (! is_subclass_of($this->class, BaseData::class)) {
            throw new CannotFindDataClass("Class {$this->class} given does not implement `BaseData::class`");
        }
    }
}
