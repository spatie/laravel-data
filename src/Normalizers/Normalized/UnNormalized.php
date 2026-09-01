<?php

namespace Spatie\LaravelData\Normalizers\Normalized;

use Spatie\LaravelData\Support\DataProperty;

class UnNormalized implements Normalized
{
    public static self $instance;

    private function __construct()
    {
    }

    public static function create(): self
    {
        return self::$instance ??= new self();
    }

    public function getProperty(string $name, DataProperty $dataProperty): mixed
    {
        return UnknownProperty::$instance;
    }
}

UnNormalized::create();
