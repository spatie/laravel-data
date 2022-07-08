<?php

namespace Spatie\LaravelData\Tests\Fakes\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\DataProperty;

class StringToUpperCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $context): string
    {
        return strtoupper($value);
    }
}
