<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;

class EnumData extends Data
{
    public function __construct(
        public DummyBackedEnum $enum
    ) {
    }
}
