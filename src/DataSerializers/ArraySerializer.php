<?php

namespace Spatie\LaravelData\DataSerializers;

use Spatie\LaravelData\Data;

class ArraySerializer implements DataSerializer
{

    public function serialize(mixed $payload): array|Data|null
    {
        if (is_array($payload)) {
            return $payload;
        }

        return null;
    }
}
