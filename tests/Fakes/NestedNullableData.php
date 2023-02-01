<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class NestedNullableData extends Data
{
    public function __construct(
        public ?SimpleData $nested
    ) {
    }
}
