<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class LazyData extends Data
{
    public static ?array $allowedIncludes;

    public function __construct(
        public string | Lazy $name
    ) {
    }

    public static function fromString(string $name): static
    {
        return new self(Lazy::create(fn () => $name));
    }

    public static function allowedRequestIncludes(): ?array
    {
        return self::$allowedIncludes;
    }
}
