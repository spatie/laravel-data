<?php

namespace Spatie\LaravelData\Mappers;

use Illuminate\Support\Collection;

interface Mapper
{
    public function map(string|int $name, Collection $properties): string|int;
}
