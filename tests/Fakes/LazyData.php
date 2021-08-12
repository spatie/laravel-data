<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class LazyData extends Data
{
    public static ?array $allowedIncludes = null;

    public function __construct(
        public string | Lazy $name
    ) {
    }

    public static function fromString(string $name): static
    {
        return new self(Lazy::create(fn () => $name));
    }

    public function allowedRequestIncludes(): ?array
    {
        return self::$allowedIncludes;
    }
}
