<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class SimpleDataWithVirtualProperty extends Data
{
    public string $string {
        get => 'virtual';
    }
}
