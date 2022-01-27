<?php

namespace Spatie\LaravelData\DataSerializers;

use Spatie\LaravelData\Data;

interface DataSerializer
{
    public function serialize(mixed $payload): array|Data|null;
}
