<?php

namespace Spatie\LaravelData\Casts;

class Uncastable
{
    static Uncastable $instance;

    private function __construct()
    {

    }

    public static function create(): self
    {
        return self::$instance ??= new self();
    }
}
