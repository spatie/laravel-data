<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class DataWithMultipleArgumentCreationMethod extends Data
{
    public function __construct(
        public string $concatenated,
    ) {
    }

    public static function fromMultiple(string $string, int $number)
    {
        return new self("{$string}_{$number}");
    }
}
