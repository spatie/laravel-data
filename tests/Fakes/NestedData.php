<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class NestedData extends Data
{
    public function __construct(
        public SimpleData $simple
    ) {
    }

    public function toUserDefinedToArray(): array
    {
        return [
            'simple' => $this->simple->toUserDefinedToArray(),
        ];
    }
}
