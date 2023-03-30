<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;

class SimpleChildDataWithMappedOutputName extends Data
{
    public function __construct(
        public int $id,
        #[MapOutputName('child_amount')]
        public float $amount
    ) {
    }

    public static function allowedRequestExcept(): ?array
    {
        return ['amount'];
    }
}
