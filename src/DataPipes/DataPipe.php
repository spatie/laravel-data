<?php

namespace Spatie\LaravelData\DataPipes;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataProperty;

interface DataPipe
{
    /**
     * @param mixed $payload
     * @param DataClass $class
     * @param Collection<string, mixed> $properties
     * @param CreationContext $creationContext
     *
     * @return Collection
     */
    public function handle(
        mixed $payload,
        DataClass $class,
        Collection $properties,
        CreationContext $creationContext
    ): Collection;
}
