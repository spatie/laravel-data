<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class NestedData extends Data
{
    public function __construct(
        public SimpleData $simple
    ) {
    }
}
