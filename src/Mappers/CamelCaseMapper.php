<?php

namespace Spatie\LaravelData\Mappers;

use Illuminate\Support\Str;

class CamelCaseMapper implements NameMapper
{
    public function map(int|string $name): string|int
    {
        return Str::camel($name);
    }
}
