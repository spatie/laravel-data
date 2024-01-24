<?php

namespace Spatie\LaravelData\Mappers;

use Illuminate\Support\Str;

class StudlyCaseMapper implements NameMapper
{
    public function map(int|string $name): string|int
    {
        return Str::studly($name);
    }
}
