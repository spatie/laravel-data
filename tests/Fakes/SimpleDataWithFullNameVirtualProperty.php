<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Data;

class SimpleDataWithFullNameVirtualProperty extends Data
{
    public string $full_name {
        get => "{$this->first_name} {$this->last_name}";
    }

    public function __construct(
        public string $first_name,
        public string $last_name,
    ) {
    }
}
