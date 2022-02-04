<?php

namespace Spatie\LaravelData\Mappers;

use Illuminate\Support\Str;

class CamelCaseToSnakeCaseMapper implements Mapper
{
    public function map(int|string $name, array $properties): string|int
    {
        if (! is_string($name)) {
            return $name;
        }

        return Str::snake($name);
    }
}
