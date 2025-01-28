<?php

namespace Spatie\LaravelData\Mappers;

use Illuminate\Support\Str;

class LowerCaseMapper implements NameMapper
{
    public function map(int|string $name): string|int
    {
        return Str::lower($name);
    }
}
