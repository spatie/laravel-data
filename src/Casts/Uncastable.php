<?php

namespace Spatie\LaravelData\Casts;

class Uncastable
{
    private static ?self $instance = null;

    private function __construct()
    {

    }

    public static function create(): self
    {
        return self::$instance ??= new self();
    }
}
