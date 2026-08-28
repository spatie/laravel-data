<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class FillFakeModelData extends Data
{
    public function __construct(
        public string $string,
    ) {
    }
}
