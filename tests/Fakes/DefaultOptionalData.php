<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class DefaultOptionalData extends Data
{
    public function __construct(
        public string | Optional $name
    ) {
    }
}
