<?php

use Spatie\LaravelData\Tests\Fakes\SimpleData;

dataset('array-access-collections', function () {
    yield "array" => [
        fn () => SimpleData::collection([
            'A', 'B', SimpleData::from('C'), SimpleData::from('D'),
        ]),
    ];

    yield "collection" => [
        fn () => SimpleData::collection([
            'A', 'B', SimpleData::from('C'), SimpleData::from('D'),
        ]),
    ];
});

dataset('collection-operations', function () {
    yield [
        'operation' => 'filter',
        'arguments' => [fn (SimpleData $data) => $data->string !== 'B'],
        'expected' => [0 => ['string' => 'A'], 2 => ['string' => 'C']],
    ];
});
