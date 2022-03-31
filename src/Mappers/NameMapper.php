<?php

namespace Spatie\LaravelData\Mappers;

interface NameMapper
{
    public function map(string|int $name): string|int;
}
