<?php

namespace Spatie\LaravelData\Mappers;

class NameProvidedMapper implements Mapper
{
    public function __construct(private string|int $name)
    {
    }

    public function map(int|string $name, array $properties): string|int
    {
        return $this->name;
    }
}
