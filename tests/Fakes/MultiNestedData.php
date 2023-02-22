<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class MultiNestedData extends Data
{
    public function __construct(
        public NestedData $nested,
        #[DataCollectionOf(NestedData::class)]
        public array $nestedCollection
    ) {
    }
}
