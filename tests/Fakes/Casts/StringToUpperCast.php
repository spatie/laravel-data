<?php

namespace Spatie\LaravelData\Tests\Fakes\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Casts\IterableItemCast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class StringToUpperCast implements Cast, IterableItemCast
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): string
    {
        return $this->castValue($value);
    }

    public function castIterableItem(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        return $this->castValue($value);
    }

    private function castValue(mixed $value): string
    {
        return strtoupper($value);
    }
}
