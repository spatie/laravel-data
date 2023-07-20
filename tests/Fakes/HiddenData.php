<?php

namespace Spatie\LaravelData\Tests\Fakes;

use Spatie\LaravelData\Attributes\Hidden;
use Spatie\LaravelData\Data;

class HiddenData extends Data
{
    public function __construct(
        public string $string,
        #[Hidden]
        public string $hidden,
    ) {
    }
}
