<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class SimpleDataWithoutConstructor extends Data
{
    public string $string;

    public static function fromString(string $string)
    {
        $data = new self();

        $data->string = $string;

        return $data;
    }
}
