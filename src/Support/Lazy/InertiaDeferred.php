<?php

namespace Spatie\LaravelData\Support\Lazy;

use Inertia\DeferProp;

class InertiaDeferred extends ConditionalLazy
{
    public function __construct(
        mixed $value,
    ) {
        $callback = match (true) {
            $value instanceof DeferProp => fn () => $value(),
            is_callable($value) => $value,
            default => fn () => $value,
        };

        parent::__construct(fn () => true, $callback);
    }

    public function resolve(): DeferProp
    {
        return new DeferProp($this->value);
    }
}
