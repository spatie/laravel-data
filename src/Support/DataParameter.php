<?php

namespace Spatie\LaravelData\Support;

use ReflectionParameter;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\OldTypes\SingleOldType;
use Spatie\LaravelData\Support\OldTypes\OldType;

class DataParameter
{
    public function __construct(
        public readonly string $name,
        public readonly bool $isPromoted,
        public readonly bool $hasDefaultValue,
        public readonly mixed $defaultValue,
        public readonly DataType $type,
    ) {
    }
}
