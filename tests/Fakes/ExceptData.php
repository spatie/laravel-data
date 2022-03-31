<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

class ExceptData extends Data
{
    public static ?array $allowedExcept;

    public function __construct(
        public string $first_name,
        public string $last_name,
    ) {
    }

    public static function allowedRequestExcept(): ?array
    {
        return self::$allowedExcept;
    }
}
