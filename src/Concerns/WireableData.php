<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Resolvers\DataFromSomethingResolver;

trait WireableData
{
    public function toLivewire(): array
    {
        return $this->toArray();
    }

    public static function fromLivewire($value): static
    {
        return app(DataFromSomethingResolver::class)
            ->ignoreMagicalMethods('fromLivewire')
            ->execute(static::class, $value);
    }
}
