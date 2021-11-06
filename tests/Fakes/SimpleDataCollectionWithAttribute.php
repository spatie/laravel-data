<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\CollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class SimpleDataCollectionWithAttribute extends Data
{
    public function __construct(
        #[CollectionOf(SimpleData::class)]
        public DataCollection $items
    ) {
    }
}
