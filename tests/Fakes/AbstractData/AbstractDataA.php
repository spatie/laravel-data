<?php

namespace Spatie\LaravelData\Tests\Fakes\AbstractData;

class AbstractDataA extends AbstractData
{
    public function __construct(
        public string $a,
    ) {
    }
}
