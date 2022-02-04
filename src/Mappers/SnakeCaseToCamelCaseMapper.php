<?php

namespace Spatie\LaravelData\Mappers;

use Illuminate\Support\Str;

class SnakeCaseToCamelCaseMapper implements Mapper
{
    public function map(int|string $name, array $properties): string|int
    {
        if(! is_string($name)){
            return $name;
        }

        return Str::camel($name);
    }
}
