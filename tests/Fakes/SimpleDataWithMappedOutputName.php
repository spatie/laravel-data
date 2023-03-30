<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class SimpleDataWithMappedOutputName extends Data
{
    public function __construct(
        public int $id,
        #[MapOutputName('paid_amount')]
        public float $amount,
        public string $anyString,
        public SimpleChildDataWithMappedOutputName $child
    ) {
    }

    public static function allowedRequestExcept(): ?array
    {
        return [
            'amount',
            'anyString',
            'child',
        ];
    }
}
