<?php

namespace Spatie\LaravelData\Tests\Fakes;

use ArgumentCountError;
use Spatie\LaravelData\Data;

class DataWithArgumentCountErrorException extends Data
{
    public function __construct(
        public string $string,
        public string $promotedOptional = 'default',
        private string $privatePromotedOptional = 'optional',
        string $optional = 'test',
    ) {
        throw new ArgumentCountError('This function expects exactly 2 arguments, 1 given.');
    }
}
