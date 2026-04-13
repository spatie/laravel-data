<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class SimpleDataWithPropertyHooks extends Data
{
    public string $virtual {
        get => 'virtual';
    }

    public string $backed = 'default' {
        get => strtoupper($this->backed);
    }

    public string $backedConstructorProperty {
        get => strtoupper($this->constructorProperty);
    }

    public function __construct(
        protected string $constructorProperty = 'default'
    ) {
    }
}
