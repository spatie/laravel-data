<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class NestedData extends Data
{
    public function __construct(
        public SimpleData $simple
    ) {
    }
}
