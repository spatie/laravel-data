<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class SimpleDataWithMappedProperty extends Data
{
    public function __construct(
        #[MapName('description', 'description')]
        public string $string
    ) {
    }
}
