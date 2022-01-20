<?php

namespace Spatie\LaravelData\Tests;

use Spatie\LaravelData\Data;

class DataWithDefaults extends Data
{
    public string $property;

    public string $default_property = 'Hello';

    public function __construct(
        public string $promoted_property,
        public string $default_promoted_property = 'Hello Again',
    ) {
    }
}
