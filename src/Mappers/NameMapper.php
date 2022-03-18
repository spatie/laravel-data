<?php

namespace Spatie\LaravelData\Mappers;

use Illuminate\Support\Collection;

interface NameMapper
{
    public function map(string|int $name): string|int;

    public function inverse(): NameMapper;
}
