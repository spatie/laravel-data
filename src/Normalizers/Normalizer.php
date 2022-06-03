<?php

namespace Spatie\LaravelData\Normalizers;

interface Normalizer
{
    public function normalize(mixed $value): ?array;
}
