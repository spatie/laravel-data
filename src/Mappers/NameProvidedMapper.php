<?php

namespace Spatie\LaravelData\Mappers;

use Illuminate\Support\Collection;

class NameProvidedMapper implements Mapper
{
    public function __construct(private string|int $name)
    {
    }

    public function map(int|string $name, Collection $properties): string|int
    {
        return $this->name;
    }
}
