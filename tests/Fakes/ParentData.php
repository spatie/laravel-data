<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;

class ParentData extends Data
{
    public function __construct(
        public Lazy|LazyData $data,
        /** @var \Spatie\LaravelData\Tests\Fakes\LazyData[] */
        public Lazy|DataCollection $collection
    ) {
    }
}
