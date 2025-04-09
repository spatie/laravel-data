<?php

namespace Spatie\LaravelData\Support;

use Spatie\LaravelData\Attributes\AutoLazy;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Transformers\Transformer;

class DataProperty
{
    public function __construct(
        public readonly string $name,
        public readonly string $className,
        public readonly DataPropertyType $type,
        public readonly bool $validate,
        public readonly bool $computed,
        public readonly bool $hidden,
        public readonly bool $isPromoted,
        public readonly bool $isReadonly,
        public readonly bool $morphable,
        public readonly ?AutoLazy $autoLazy,
        public readonly bool $hasDefaultValue,
        public readonly mixed $defaultValue,
        public readonly ?Cast $cast,
        public readonly ?Transformer $transformer,
        public readonly ?string $inputMappedName,
        public readonly ?string $outputMappedName,
        public readonly DataAttributesCollection $attributes,
    ) {
    }
}
