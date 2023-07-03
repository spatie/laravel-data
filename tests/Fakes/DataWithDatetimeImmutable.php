<?php

namespace Spatie\LaravelData\Tests\Fakes;

use DateTimeImmutable;
use Spatie\LaravelData\Attributes\Validation\AfterOrEqual;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

class DataWithDatetimeImmutable extends Data
{
    #[Required, BeforeOrEqual('date_to'), WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
    public DateTimeImmutable $date_from;

    #[Required, AfterOrEqual('date_from'), WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
    public DateTimeImmutable $date_to;
}
