<?php

namespace Spatie\LaravelData\Support\Creation\Actions;

use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalized\UnNormalized;
use Spatie\LaravelData\Normalizers\Normalizer;

class NormalizePayloadAction
{
    /**
     * @param array<int, Normalizer> $normalizers
     */
    public function __construct(
        protected array $normalizers,
    ) {
    }

    public function execute(mixed $value): array|Normalized
    {
        if ($value === null) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Normalized) {
            return $value;
        }

        foreach ($this->normalizers as $normalizer) {
            $normalized = $normalizer->normalize($value);

            if ($normalized !== null) {
                return $normalized;
            }
        }

        return UnNormalized::$instance;
    }
}
