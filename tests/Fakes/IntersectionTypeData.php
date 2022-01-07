<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelData\Data;

class IntersectionTypeData extends Data
{
    public function __construct(
        public Arrayable & \Countable $intersection,
    ) {
    }
}
