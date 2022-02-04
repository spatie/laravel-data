<?php

namespace Spatie\LaravelData\Serializers;

use Spatie\LaravelData\Data;

interface DataSerializer
{
    public function serialize(string $class, mixed $payload): ?Data;
}
