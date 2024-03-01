<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class ComputedData extends Data
{
    public string $name = 'computed';

    public function __construct(
        public string $first_name,
        public string $last_name,
    ) {
        $this->name = $this->first_name . ' ' . $this->last_name;
    }
}
