<?php

namespace Spatie\LaravelData\Mappers;

use Illuminate\Support\Str;

class KebabCaseMapper implements NameMapper
{
    public function map(int|string $name): string|int
    {
        return Str::kebab($name);
    }
}
