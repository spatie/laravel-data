<?php

namespace Spatie\LaravelData\Tests\Fakes\AbstractData;

class AbstractDataB extends AbstractData
{
    public function __construct(
        public string $b,
    ) {
    }
}
