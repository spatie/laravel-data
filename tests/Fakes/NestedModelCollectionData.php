<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class NestedModelCollectionData extends Data
{
    public function __construct(
        /** @var \Spatie\LaravelData\Tests\Fakes\ModelData[] */
        public DataCollection $models
    ) {
    }
}
