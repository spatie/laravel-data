<?php

use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

dataset('array-access-collections', function () {
    yield "array" => [
        fn () => SimpleData::collect([
            'A', 'B', SimpleData::from('C'), SimpleData::from('D'),
        ], DataCollection::class),
    ];

    yield "collection" => [
        fn () => SimpleData::collect([
            'A', 'B', SimpleData::from('C'), SimpleData::from('D'),
        ], DataCollection::class),
    ];
});

dataset('collection-operations', function () {
    yield [
        'operation' => 'filter',
        'arguments' => [fn (SimpleData $data) => $data->string !== 'B'],
        'expected' => [0 => ['string' => 'A'], 2 => ['string' => 'C']],
    ];
});
