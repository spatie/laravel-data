<?php

namespace Spatie\LaravelData\Tests\Fakes;

class FakeInjectable
{
    public function __construct(
        public readonly mixed $value,
    ) {
    }

    public static function setup(mixed $value): void
    {
        app()->bind(self::class, fn () => new self($value));
    }
}
