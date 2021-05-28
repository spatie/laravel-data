<?php

namespace Spatie\LaravelData\Tests\Fakes;

use App\Support\Data\Lazy;
use Spatie\LaravelData\Data;

class TestResource extends Data
{
    public function __construct(
        public string $string,
        public ?string $nullable,
        public string | Lazy $lazy,
        public array $collection,
    ) {
    }

//    public static function make(array $resource): static
//    {
//        return new self(
//            $resource['string'] => $resource['string']
//        )
//    }
}
