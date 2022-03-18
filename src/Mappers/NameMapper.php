<?php

namespace Spatie\LaravelData\Mappers;

use Illuminate\Support\Collection;

interface NameMapper
{
    public function map(string|int $name, Collection $properties): string|int;
}
