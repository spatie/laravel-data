<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Tests\Fakes\Collections\SimpleDataCollectionWithAnotations;

class DataWithSimpleDataCollectionWithAnotations extends Data
{
    public function __construct(
        public SimpleDataCollectionWithAnotations $collection
    ) {
    }
}
