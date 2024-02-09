<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataContainer;

class LazyData extends Data
{
    protected static ?array $allowedIncludes = null;

    public function __construct(
        public string|Lazy $name
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

    public static function setAllowedIncludes(?array $allowedIncludes): void
    {
        self::$allowedIncludes = $allowedIncludes;

        // Ensure cached config is cleared
        app(DataConfig::class)->reset();
        DataContainer::get()->reset();
    }
}
