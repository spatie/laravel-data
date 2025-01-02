<?php

namespace Spatie\LaravelData\Tests\Fakes\PropertyMorphableData;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class NestedPropertyMorphableData extends Data
{
    public function __construct(
        /** @var AbstractPropertyMorphableData[] */
        public ?DataCollection $nestedCollection,
    ) {
    }
}
