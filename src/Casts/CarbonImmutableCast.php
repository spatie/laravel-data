<?php

namespace Spatie\LaravelData\Casts;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use Spatie\LaravelData\Support\DataProperty;

class CarbonImmutableCast extends DateTimeInterfaceCast
{
    protected function findType(DataProperty $property)
    {
        return CarbonImmutable::class;
    }
}
