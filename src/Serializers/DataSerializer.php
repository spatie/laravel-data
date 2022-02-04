<?php

namespace Spatie\LaravelData\Serializers;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataClass;

interface DataSerializer
{
    public function serialize(string $class, mixed $payload): ?Data;
}
