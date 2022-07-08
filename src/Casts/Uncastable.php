<?php

namespace Spatie\LaravelData\Casts;

class Uncastable
{
    public static function create(): self
    {
        return new self();
    }
}
