<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Wrapping\Wrap;

class SimpleDataWithWrap extends Data
{
    public function __construct(
        public string $string
    ) {
    }

    public static function fromString(string $string)
    {
        return new self($string);
    }

    public function defaultWrap(): string
    {
        return 'wrap';
    }
}
