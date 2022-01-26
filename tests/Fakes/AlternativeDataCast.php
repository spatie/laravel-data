<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\DataProperty;

class AlternativeDataCast implements Cast
{
    public function cast(DataProperty $property, mixed $value): mixed
    {
    }
}
