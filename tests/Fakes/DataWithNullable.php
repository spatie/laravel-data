<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class DataWithNullable extends Data
{
    public function __construct(
        public string $string,
        public ?string $nullableString,
    ) {
    }
}
