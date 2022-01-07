<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class ReadonlyData extends Data
{
    public function __construct(
        public readonly string $string,
    ) {
    }
}
