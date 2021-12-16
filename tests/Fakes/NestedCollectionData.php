<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class NestedCollectionData extends Data
{
    public function __construct(
        /** @var DataCollection<\Spatie\LaravelData\Tests\Fakes\SimpleData> */
        public DataCollection $items
    ) {
    }
}
