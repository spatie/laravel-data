<?php

namespace Spatie\LaravelData\Casts;

class Uncastable
{
    public static function create(): static
    {
        return new static();
    }
}
