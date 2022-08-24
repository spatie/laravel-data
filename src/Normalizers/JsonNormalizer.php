<?php

namespace Spatie\LaravelData\Normalizers;

use JsonException;

class JsonNormalizer implements Normalizer
{
    public function normalize(mixed $value): ?array
    {
        if (! is_string($value)) {
            return null;
        }

        try {
            return json_decode($value, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }
    }
}
