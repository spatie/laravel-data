<?php

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Tests\Fakes\ComplicatedData;
use Spatie\LaravelData\Tests\Fakes\NestedData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('can transform collected data', function () {
    $data = new ComplicatedData(
        42,
        42,
        true,
        3.14,
        'Hello World',
        [1, 1, 2, 3, 5, 8],
        null,
        Optional::create(),
        42,
        CarbonImmutable::create(1994, 05, 16),
        new DateTime('1994-05-16T12:00:00+01:00'),
        new SimpleData('hello'),
        new DataCollection(NestedData::class, [
            new NestedData(new SimpleData('I')),
            new NestedData(new SimpleData('am')),
            new NestedData(new SimpleData('groot')),
        ]),
        [
            new NestedData(new SimpleData('I')),
            new NestedData(new SimpleData('am')),
            new NestedData(new SimpleData('groot')),
        ],
    );

    // Preload data class info
    app(DataConfig::class)->getDataClass(ComplicatedData::class);
    app(DataConfig::class)->getDataClass(SimpleData::class);
    app(DataConfig::class)->getDataClass(NestedData::class);

    $collection = Collection::times(1000, fn () => clone $data);

    $dataCollection = ComplicatedData::collect($collection, DataCollection::class);

    bench(
        fn () => $dataCollection->toArray(),
        fn () => $dataCollection->toCollection()->map(fn (ComplicatedData $data) => $data->toUserDefinedToArray()),
        name: 'collection',
        times: 10
    );

    bench(
        fn() => $data->toArray(),
        fn() => $data->toUserDefinedToArray(),
        name: 'single',
    );
});

function benchSingle(Closure $closure, $times): float{
    $start = microtime(true);

    for ($i = 0; $i < $times; $i++) {
        $closure();
    }

    $end = microtime(true);

    return ($end - $start) / $times;
}

function bench(Closure $data, Closure $userDefined, string $name, $times = 100): void
{
    $dataBench = benchSingle($data, $times);
    $userDefinedBench = benchSingle($userDefined, $times);

    dump("{$name} data - " . number_format($dataBench, 10));
    dump("{$name} user defined - " . number_format($userDefinedBench, 10));
    dump("{$name} data is " . round($dataBench / $userDefinedBench,0) . " times slower than user defined");

}


