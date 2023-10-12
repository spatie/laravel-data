<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnumWithOptions;

class SimpleDataWithEloquentExcludedTransformerData extends Data
{
    public function __construct(
        public readonly string $string,
        public readonly DummyBackedEnumWithOptions $enum,
        public readonly DummyBackedEnum $normal_enum,
    ) {
    }
}
