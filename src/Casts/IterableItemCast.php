<?php

namespace Spatie\LaravelData\Casts;

use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

interface IterableItemCast
{
    /**
     * @param array<string, mixed> $properties
     */
    public function castIterableItem(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed;
}
