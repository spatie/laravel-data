<?php

namespace Spatie\LaravelData\Normalizers;

class ArrayNormalizer extends Normalizer
{
    public function normalize(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        return $value;
    }
}
