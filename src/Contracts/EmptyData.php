<?php

namespace Spatie\LaravelData\Contracts;

interface EmptyData
{
    public static function empty(array $extra = []): array;
}
