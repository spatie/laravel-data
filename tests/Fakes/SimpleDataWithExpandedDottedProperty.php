<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class SimpleDataWithExpandedDottedProperty extends Data
{
    public function __construct(
        #[MapName('dotted.description', expandDotNotation: true)]
        public string $string
    ) {
    }
}
