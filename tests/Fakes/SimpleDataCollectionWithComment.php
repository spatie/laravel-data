<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class SimpleDataCollectionWithComment extends Data
{
    public function __construct(
        /** @var \Spatie\LaravelData\Tests\Fakes\SimpleData[] */
        public DataCollection $items
    ) {
    }
}
