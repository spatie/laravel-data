<?php
namespace Spatie\LaravelData\Tests\Fakes\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\Castables\SimpleCastableClassString;

class SimpleCastableClassStringCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $context): mixed
    {
        return new SimpleCastableClassString($value);
    }
}
