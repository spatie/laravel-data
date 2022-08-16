<?php

namespace Spatie\LaravelData\Normalizers;

use Illuminate\Contracts\Support\Arrayable;

class ArrayableNormalizer implements Normalizer
{
    public function normalize(mixed $value): ?array
    {
        if (! $value instanceof Arrayable) {
            return null;
        }

        return $value->toArray();
    }
}
