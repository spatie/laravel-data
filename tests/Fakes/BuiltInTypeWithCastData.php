<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataProperty;

class BuiltInTypeWithCastData extends Data
{
    public function __construct(
        #[WithCast(MoneyCast::class)]
        public int $money,
    ) {
    }
}

class MoneyCast implements Cast
{
    public function cast(DataProperty $property, mixed $value): int
    {
        return (int) ($value * 100);
    }
}
