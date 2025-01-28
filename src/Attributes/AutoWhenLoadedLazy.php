<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Closure;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Lazy\ConditionalLazy;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AutoWhenLoadedLazy extends AutoLazy
{
    public function __construct(
        public ?string $relation = null,
    ) {
    }

    public function build(Closure $castValue, mixed $payload, DataProperty $property, mixed $value): ConditionalLazy
    {
        $relation = $this->relation ?? $property->name;

        return Lazy::when(fn () => $payload->relationLoaded($relation), fn () => $castValue(
            $payload->getRelation($relation)
        ));
    }
}
