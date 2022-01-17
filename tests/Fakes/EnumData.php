<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class EnumData extends Data
{
    public function __construct(
        public FakeEnum $enum,
    ) {
    }
}
