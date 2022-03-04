<?php

namespace Spatie\LaravelData\Normalizers;

abstract class Normalizer
{
    abstract public function normalize(mixed $value): ?array;
}
