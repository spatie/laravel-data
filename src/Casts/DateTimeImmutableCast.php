<?php

namespace Spatie\LaravelData\Casts;

use DateTime;
use DateTimeImmutable;
use Spatie\LaravelData\Support\DataProperty;

class DateTimeImmutableCast extends DateTimeInterfaceCast
{
    protected function findType(DataProperty $property)
    {
        return DateTimeImmutable::class;
    }
}
