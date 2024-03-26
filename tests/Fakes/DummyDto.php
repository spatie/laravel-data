<?php

namespace Spatie\LaravelData\Tests\Fakes;

class DummyDto
{
    public function __construct(
        public string $artist,
        public string $name,
        public int $year
    ) {
    }

    public static function rick(): static
    {
        return new self('Rick Astley', 'Never gonna give you up', 1987);
    }

    public static function bon(): static
    {
        return new self('Bon Jovi', 'Living on a prayer', 1986);
    }

    public static function darude(): static
    {
        return new self('Darude', 'sandstorm', 2000);
    }
}
