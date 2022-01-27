<?php

namespace Spatie\LaravelData\DataSerializers;

use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelData\Data;

class ArrayableSerializer implements DataSerializer
{
    public function serialize(mixed $payload): array|Data|null
    {
        if (! $payload instanceof Arrayable) {
            return null;
        }

        return $payload->toArray();
    }
}
