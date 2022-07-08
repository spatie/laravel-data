<?php

namespace Spatie\LaravelData\Mappers;

use Illuminate\Support\Str;

class CamelCaseMapper implements NameMapper
{
    public function map(int|string $name): string|int
    {
        if (! is_string($name)) {
            return $name;
        }

        return Str::camel($name);
    }
}
