<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class LazyResource extends Data
{
    public function __construct(
        public string | Lazy $name
    ) {
    }
}
