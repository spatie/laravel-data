<?php

namespace Spatie\LaravelData\Normalizers\Normalized;

use Spatie\LaravelData\Support\DataProperty;

interface Normalized
{
    public function getProperty(string $name, DataProperty $dataProperty): mixed;
}
