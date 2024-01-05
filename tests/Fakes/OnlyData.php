<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\DataContainer;

class OnlyData extends Data
{
    protected static ?array $allowedOnly = null;

    public function __construct(
        public string $first_name,
        public string $last_name,
    ) {
    }

    public static function allowedRequestOnly(): ?array
    {
        return self::$allowedOnly;
    }

    public static function setAllowedOnly(?array $allowedOnly): void
    {
        self::$allowedOnly = $allowedOnly;

        // Ensure cached config is cleared
        app(DataConfig::class)->reset();
        DataContainer::get()->reset();
    }
}
