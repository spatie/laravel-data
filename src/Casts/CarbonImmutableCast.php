<?php

namespace Spatie\LaravelData\Casts;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Support\DataProperty;

class CarbonImmutableCast extends DateTimeInterfaceCast
{
    protected function findType(DataProperty $property)
    {
        return CarbonImmutable::class;
    }
}
