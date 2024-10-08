<?php

namespace Spatie\LaravelData\Casts;

use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

interface Cast
{
    /**
     * @param array<string, mixed> $properties
     */
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed;
}
