<?php

namespace Spatie\LaravelData\Tests\Normalizers;

use Spatie\LaravelData\Normalizers\Normalized\Normalized;
use Spatie\LaravelData\Normalizers\Normalizer;
use Spatie\LaravelData\Support\DataProperty;

class NormalizedNormalizer implements Normalizer
{
    public function normalize(mixed $value): null|array|Normalized
    {
        return new class ($value) implements Normalized {
            public function __construct(
                protected array $value
            ) {
            }

            public function getProperty(string $name, DataProperty $dataProperty): mixed
            {
                return $this->value[$name];
            }
        };
    }
}
