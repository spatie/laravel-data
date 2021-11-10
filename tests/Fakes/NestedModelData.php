<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class NestedModelData extends Data
{
    public function __construct(
        public ModelData $model
    ) {
    }
}
