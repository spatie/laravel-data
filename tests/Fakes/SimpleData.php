<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\Max;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\RequestData;

class SimpleData extends Data implements RequestData
{
    public function __construct(
        public string $string
    ) {
    }

    public static function create($resource): static
    {
        if(is_string($resource)) {
            return new self($resource);
        }

        return parent::create($resource);
    }
}
