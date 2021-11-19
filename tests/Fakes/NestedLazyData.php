<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class NestedLazyData extends Data
{
    public function __construct(
        public SimpleData|Lazy $simple
    ) {
    }
}
