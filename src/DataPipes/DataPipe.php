<?php

namespace Spatie\LaravelData\DataPipes;

use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;

interface DataPipe
{
    /**
     * @param array<array-key, mixed> $properties
     *
     * @return array<array-key, mixed>
     */
    public function handle(mixed $payload, DataClass $class, array $properties, CreationContext $creationContext): array;
}
