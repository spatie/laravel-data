<?php

namespace Spatie\LaravelData\Support\Lazy;

use Inertia\DeferProp;

class InertiaDeferred extends ConditionalLazy
{
    private ?string $group;

    public function __construct(
        mixed $value,
        ?string $group = null,
    ) {
        $callback = match (true) {
            $value instanceof DeferProp => fn () => $value(),
            is_callable($value) => $value,
            default => fn () => $value,
        };

        parent::__construct(fn () => true, $callback);

        $this->group = $value instanceof DeferProp && $value->group() !== 'default'
            ? $value->group()
            : $group;
    }

    public function resolve(): DeferProp
    {
        return new DeferProp($this->value, $this->group);
    }
}
