<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class LazyData extends Data
{
    public function __construct(
        public string | Lazy $name
    ) {
    }

    public static function create(mixed $name): static
    {
        return new self(
            Lazy::create(fn () => $name)
        );
    }
}
