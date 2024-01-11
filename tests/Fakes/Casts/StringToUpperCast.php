<?php

namespace Spatie\LaravelData\Tests\Fakes\Casts;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class StringToUpperCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, Collection $properties, CreationContext $context): string
    {
        return strtoupper($value);
    }
}
