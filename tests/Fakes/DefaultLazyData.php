<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataContainer;

class DefaultLazyData extends Data
{
    protected static ?array $allowedExcludes = null;

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

    public static function allowedRequestExcludes(): ?array
    {
        return self::$allowedExcludes;
    }

    public static function setAllowedExcludes(?array $allowedExcludes): void
    {
        self::$allowedExcludes = $allowedExcludes;

        // Ensure cached config is cleared
        app(DataConfig::class)->reset();
        DataContainer::get()->reset();    }
}
