<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class EnumCastData extends Data
{
    public function __construct(
        #[WithCast(EnumCast::class)]
        public DummyBackedEnum $enum
    ) {}
}
