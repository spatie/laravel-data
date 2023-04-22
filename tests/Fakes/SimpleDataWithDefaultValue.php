<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class SimpleDataWithDefaultValue extends Data
{
    public function __construct(
        public string $string = 'default'
    ) {
    }

    public static function fromString(string $string): self
    {
        return new self($string);
    }
}
