<?php

namespace Spatie\LaravelData\Contracts;

interface EmptyStringData
{
    public static function empty(array $extra = []): array;
}
