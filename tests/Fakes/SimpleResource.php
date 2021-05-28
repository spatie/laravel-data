<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class SimpleResource extends Data
{
    public function __construct(
        public string $string
    ) {
    }

    public static function create($resource): static
    {
        return new self($resource);
    }
}
