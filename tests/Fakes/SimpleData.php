<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\Max;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\RequestData;

class SimpleData extends Data implements RequestData
{
    public function __construct(
        #[Max(10)]
        public string $string
    ) {
    }

    public static function create($resource): static
    {
        return new self($resource);
    }
}
