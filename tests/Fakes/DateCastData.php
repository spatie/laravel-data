<?php

namespace Spatie\LaravelData\Tests\Fakes;

use DateTimeImmutable;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

class DateCastData extends Data
{
    public function __construct(
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
        public DateTimeImmutable $date
    ) {}
}
