<?php

use Spatie\LaravelData\Tests\Fakes\SimpleData;

dataset('collection-operations', function () {
    yield [
        'operation' => 'filter',
        'arguments' => [fn (SimpleData $data) => $data->string !== 'B'],
        'expected' => [0 => ['string' => 'A'], 2 => ['string' => 'C']],
    ];
});
