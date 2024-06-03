<?php


use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\Lazy\DefaultLazy;
use Spatie\LaravelData\Tests\Fakes\LazyData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

use function Spatie\Snapshots\assertMatchesSnapshot;

it('can serialize and unserialize a data object', function () {
    $object = SimpleData::from('Hello world');

    $serialized = serialize($object);

    assertMatchesSnapshot($serialized);

    $unserialized = unserialize($serialized);

    expect($unserialized)->toBeInstanceOf(SimpleData::class);
    expect($unserialized->string)->toEqual('Hello world');
});

it('can serialize and unserialize a data object with additional data', function () {
    $object = SimpleData::from('Hello world')->additional([
        'int' => 69,
    ]);

    $serialized = serialize($object);

    assertMatchesSnapshot($serialized);

    $unserialized = unserialize($serialized);

    expect($unserialized)->toBeInstanceOf(SimpleData::class);
    expect($unserialized->string)->toEqual('Hello world');
    expect($unserialized->getAdditionalData())->toEqual(['int' => 69]);
});

it('can serialize and unserialize a data collection', function () {
    $collection = new DataCollection(SimpleData::class, ['A', 'B']);

    $serialized = serialize($collection);

    assertMatchesSnapshot($serialized);

    $unserialized = unserialize($serialized);

    expect($unserialized)->toBeInstanceOf(DataCollection::class);
    expect($unserialized)->toEqual(new DataCollection(SimpleData::class, ['A', 'B']));
});

it('will keep context attached to data when serialized', function () {
    $object = LazyData::from('Hello world')->include('name');

    $unserialized = unserialize(serialize($object));

    expect($unserialized)->toBeInstanceOf(LazyData::class);
    expect($unserialized->toArray())->toMatchArray(['name' => 'Hello world']);
});

it('is possible to add partials with closures and serialize them', function () {
    $object = LazyData::from('Hello world')->includeWhen(
        'name',
        fn (LazyData $data) => $data->name instanceof DefaultLazy
    );

    $unserialized = unserialize(serialize($object));

    expect($unserialized)->toBeInstanceOf(LazyData::class);
    expect($unserialized->toArray())->toMatchArray(['name' => 'Hello world']);
});

it('is possible to serialize conditional lazy properties', function () {
    $object = new LazyData(Lazy::when(
        fn () => true,
        fn () => 'Hello world'
    ));

    $unserialized = unserialize(serialize($object));

    expect($unserialized)->toBeInstanceOf(LazyData::class);
    expect($unserialized->toArray())->toMatchArray(['name' => 'Hello world']);
});
