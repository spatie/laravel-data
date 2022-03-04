<?php

namespace Spatie\LaravelData\Normalizers;

abstract class Normalizer
{
    public abstract function normalize(mixed $value): ?array;
}
