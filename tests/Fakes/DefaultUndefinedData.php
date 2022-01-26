<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Undefined;

class DefaultUndefinedData extends Data
{
    public function __construct(
        public string | Undefined $name
    ) {
    }
}
