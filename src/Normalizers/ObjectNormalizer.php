<?php

namespace Spatie\LaravelData\Normalizers;

use stdClass;

class ObjectNormalizer extends Normalizer
{
    public function normalize(mixed $value): ?array
    {
        if (! $value instanceof stdClass) {
            return null;
        }

        return (array) $value;
    }
}
