<?php

namespace Spatie\LaravelData\Casts;

use DateTime;
use Spatie\LaravelData\Support\DataProperty;

class DateTimeCast extends DateTimeInterfaceCast
{
    protected function findType(DataProperty $property)
    {
        return DateTime::class;
    }
}
