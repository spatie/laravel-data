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
            $maybe_array = json_decode($value, associative: true, flags: JSON_THROW_ON_ERROR);
            return is_array($maybe_array) ? $maybe_array : null;
        } catch (JsonException) {
            return null;
        }
    }
}
