<?php

namespace Spatie\LaravelData\Mappers;

use Illuminate\Support\Str;

class SnakeCaseMapper implements NameMapper
{
    public function map(int|string $name): string|int
    {
        return Str::snake($name);
    }
}
