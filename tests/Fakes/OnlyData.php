<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class OnlyData extends Data
{
    public static ?array $allowedOnly;

    public function __construct(
        public string $first_name,
        public string $last_name,
    ) {
    }

    public static function allowedRequestOnly(): ?array
    {
        return self::$allowedOnly;
    }
}
