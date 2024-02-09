<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Resource;

class SimpleResource extends Resource
{
    public function __construct(
        public string $string
    ) {
    }

    public static function fromString(string $string): self
    {
        return new self($string);
    }
}
