<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Attributes\AutoLazy;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Lazy\ConditionalLazy;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AutoWhenAppendedLazy extends AutoLazy
{
    public function __construct(
        protected ?string $accessor = null,
    ) {
    }

    public function build(Closure $castValue, mixed $payload, DataProperty $property, mixed $value): ConditionalLazy
    {
        $accessor = $this->forAccessor($property);

        return Lazy::when(
            fn () => $payload instanceof Model && in_array($accessor, $payload->getAppends()),
            fn () => $castValue($payload->getAttribute($accessor))
        );
    }

    public function forAccessor(DataProperty $property): ?string
    {
        return $this->accessor ?? $property->name;
    }
}
