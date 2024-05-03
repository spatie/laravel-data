<?php

namespace Spatie\LaravelData\Normalizers\Normalized;

class UnknownProperty
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
