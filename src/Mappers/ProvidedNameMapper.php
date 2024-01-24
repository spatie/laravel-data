<?php

namespace Spatie\LaravelData\Mappers;

class ProvidedNameMapper implements NameMapper
{
    public function __construct(protected string|int $name)
    {
    }

    public function map(int|string $name): string|int
    {
        return $this->name;
    }
}
