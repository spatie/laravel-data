<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class FillNestedModelData extends Data
{
    public function __construct(
        public FillFakeModelData $fakeModel,
    ) {
    }
}
