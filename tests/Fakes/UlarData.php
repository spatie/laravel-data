<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class UlarData extends Data
{
    public function __construct(
        public string $string,
        public ?CircData $circ,
    )
    {
    }
}
