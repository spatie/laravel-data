<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class CircData extends Data
{
    public function __construct(
        public string $string,
        public ?UlarData $ular,
    )
    {
    }
}
