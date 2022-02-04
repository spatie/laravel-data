<?php

namespace Spatie\LaravelData\Mappers;

interface Mapper
{
    public function map(string|int $name, array $properties): string|int;
}
