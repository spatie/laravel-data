<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class SimpleDataWithBackedProperty extends Data
{
    public string $string = 'default' {
        get => "{$this->string} (backed)";
    }
}
