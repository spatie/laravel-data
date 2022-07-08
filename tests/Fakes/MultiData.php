<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class MultiData extends Data
{
    public function __construct(
        public string $first,
        public string $second,
    ) {
    }
}
