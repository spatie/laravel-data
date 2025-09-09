<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class SimpleDataWithMappedDottedProperty extends Data
{
    public function __construct(
        #[MapName('dotted.description', 'dotted.description')]
        public string $string
    ) {
    }
}
