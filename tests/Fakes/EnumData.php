<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class EnumData extends Data
{
    public function __construct(
        public DummyBackedEnum $enum
    ) {
    }
}
