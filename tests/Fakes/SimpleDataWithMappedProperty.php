<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;

class SimpleDataWithMappedProperty extends Data
{
    public function __construct(
        #[MapInputName('description')]
        public string $string
    ) {
    }
}
