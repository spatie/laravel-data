<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class DefaultLazyData extends Data
{
    public static ?array $allowedExcludes = null;

    public function __construct(
        public string | Lazy $name
    ) {
    }

    public static function fromString(string $name): static
    {
        return new self(
            Lazy::create(fn () => $name)->defaultIncluded()
        );
    }

    public function allowedRequestExcludes(): ?array
    {
        return self::$allowedExcludes;
    }
}
