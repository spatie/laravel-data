<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class EnumData extends Data
{
    public function __construct(
        public DummyBackedEnum $enum
    ) {
    }
}
