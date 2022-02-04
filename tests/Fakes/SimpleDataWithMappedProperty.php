<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\MapFrom;
use Spatie\LaravelData\Data;

class SimpleDataWithMappedProperty extends Data
{
    public function __construct(
        #[MapFrom('description')]
        public string $string
    ) {
    }
}
