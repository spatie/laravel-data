<?php

namespace Spatie\LaravelData\Support\Creation;

use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalizer;

class SourceResolver
{
    /**
     * @param array<int, Normalizer> $normalizers
     */
    public static function resolve(
        string $dataClass,
        mixed $value,
        array $normalizers
    ): array|Normalized {
        if ($value === null) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Normalized) {
            return $value;
        }

        foreach ($normalizers as $normalizer) {
            $normalized = $normalizer->normalize($value);

            if ($normalized !== null) {
                return $normalized;
            }
        }

        throw CannotCreateData::noNormalizerFound($dataClass, $value);
    }
}
