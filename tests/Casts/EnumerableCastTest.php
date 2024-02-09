<?php


use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('will not cast an object which is already a collection', function () {
    $dataClass = new class () extends Data {
        public Collection $collection;
    };

    $data = $dataClass::from([
        'collection' => collect(['a', 'b']),
    ]);

    expect($data->collection)->toEqual(collect(['a', 'b']));
});

it('will cast an array to collection', function () {
    $dataClass = new class () extends Data {
        public Collection $collection;
    };

    $data = $dataClass::from([
        'collection' => ['a', 'b'],
    ]);

    expect($data->collection)->toEqual(collect(['a', 'b']));
});

it('will cast an array to the specified collection type', function () {
    $dataClass = new class () extends Data {
        public LazyCollection $collection;
    };

    $data = $dataClass::from([
        'collection' => ['a', 'b'],
    ]);

    expect($data->collection)->toEqual(new LazyCollection(['a', 'b']));
});

it('will default to a collection when no clear type is specified', function () {
    $dataClass = new class () extends Data {
        public Collection|array $collection;
    };

    $data = $dataClass::from([
        'collection' => ['a', 'b'],
    ]);

    expect($data->collection)->toEqual(collect(['a', 'b']));
});

it('will never intervene with data collections', function () {
    class TestDataCollectionCastWithDataCollectable extends Data {
        /** @var Collection<SimpleData>  */
        public Collection $collection;
    }

    $data = TestDataCollectionCastWithDataCollectable::from([
        'collection' => ['a', 'b'],
    ]);

    expect($data->collection)->toEqual(collect([
        SimpleData::fromString('a'),
        SimpleData::fromString('b'),
    ]));
});
