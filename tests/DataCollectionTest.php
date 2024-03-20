<?php

use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\LazyCollection;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Tests\Fakes\Collections\CustomCollection;
use Spatie\LaravelData\Tests\Fakes\LazyData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

use function Spatie\Snapshots\assertMatchesSnapshot;

it('can filter a collection', function () {
    $collection = new DataCollection(SimpleData::class, ['A', 'B']);

    $filtered = $collection->filter(fn (SimpleData $data) => $data->string === 'A')->toArray();

    expect([
        ['string' => 'A'],
    ])
        ->toMatchArray($filtered);
});

it('can reject items within a collection', function () {
    $collection = new DataCollection(SimpleData::class, ['A', 'B']);

    $filtered = $collection->reject(fn (SimpleData $data) => $data->string === 'B')->toArray();

    expect([
        ['string' => 'A'],
    ])
        ->toMatchArray($filtered);
});


it('it can put items through a paginated data collection', function () {
    $collection = new PaginatedDataCollection(
        SimpleData::class,
        new LengthAwarePaginator(['A', 'B'], 2, 15),
    );

    $filtered = $collection->through(fn (SimpleData $data) => new SimpleData("{$data->string}x"))->toArray();

    expect($filtered['data'])->toMatchArray([
        ['string' => 'Ax'],
        ['string' => 'Bx'],
    ]);
});

it('is iteratable', function () {
    $collection = new DataCollection(SimpleData::class, [
        'A', 'B', 'C', 'D',
    ]);

    $letters = [];

    foreach ($collection as $item) {
        $letters[] = $item->string;
    }

    expect($letters)->toMatchArray(['A', 'B', 'C', 'D']);
});

it('has array access', function () {
    $collection = SimpleData::collect([
        'A', 'B', SimpleData::from('C'), SimpleData::from('D'),
    ], DataCollection::class);

    // Count
    expect($collection)->toHaveCount(4);

    // Offset exists
    expect($collection[3])->not->toBeEmpty();

    expect(empty($collection[5]))->toBeTrue();

    // Offset get
    $dataA = SimpleData::from('A');
    $dataA->getDataContext();

    expect($collection[0])->toEqual($dataA);

    $dataD = SimpleData::from('D');
    $dataD->getDataContext();

    expect($collection[3])->toEqual($dataD);

    if ($collection->items() instanceof AbstractPaginator || $collection->items() instanceof CursorPaginator) {
        return;
    }

    // Offset set
    $collection[2] = 'And now something completely different';
    $collection[4] = 'E';

    $otherData = SimpleData::from('And now something completely different');
    $otherData->getDataContext();

    expect($collection[2])->toEqual($otherData);

    $dataE = SimpleData::from('E');
    $dataE->getDataContext();

    expect($collection[4])->toEqual($dataE);

    // Offset unset
    unset($collection[4]);

    expect($collection)->toHaveCount(4);
});

it('can update data properties within a collection', function () {
    LazyData::setAllowedIncludes(null);

    $collection = new DataCollection(LazyData::class, [
        LazyData::from('Never gonna give you up!'),
    ]);

    expect($collection->include('name')->toArray())
        ->toMatchArray([
            ['name' => 'Never gonna give you up!'],
        ]);

    $collection[0]->name = 'Giving Up on Love';

    expect($collection->include('name')->toArray())
        ->toMatchArray([
            ['name' => 'Giving Up on Love'],
        ]);

    $collection[] = LazyData::from('Cry for help');

    expect($collection->include('name')->toArray())
        ->toMatchArray([
            ['name' => 'Giving Up on Love'],
            ['name' => 'Cry for help'],
        ]);

    unset($collection[0]);

    expect($collection->include('name')->toArray())
        ->toMatchArray([
            1 => ['name' => 'Cry for help'],
        ]);
});

it('can create a data collection from a Lazy Collection', function () {
    $lazyCollection = new LazyCollection(function () {
        $items = [
            'Never gonna give you up!',
            'Giving Up on Love',
        ];

        foreach ($items as $item) {
            yield $item;
        }
    });

    $collection = new DataCollection(SimpleData::class, $lazyCollection);

    expect($collection)->items()
        ->toMatchArray([
            SimpleData::from('Never gonna give you up!'),
            SimpleData::from('Giving Up on Love'),
        ]);

    $transformed = $collection->through(function (SimpleData $data) {
        $data->string = strtoupper($data->string);

        return $data;
    })->filter(fn (SimpleData $data) => $data->string === strtoupper('Never gonna give you up!'))->toArray();

    expect($transformed)->toMatchArray([
        ['string' => strtoupper('Never gonna give you up!')],
    ]);
});

it('can convert a data collection into a Laravel collection', function () {
    expect(
        collect([
            SimpleData::from('A'),
            SimpleData::from('B'),
            SimpleData::from('C'),
        ])
    )
        ->toEqual(
            (new DataCollection(SimpleData::class, ['A', 'B', 'C']))->toCollection()
        );
});

it('can reset the keys', function () {
    $collection = new DataCollection(SimpleData::class, [
        1 => SimpleData::from('a'),
        3 => SimpleData::from('b'),
    ]);

    expect(
        new DataCollection(SimpleData::class, [
            0 => SimpleData::from('a'),
            1 => SimpleData::from('b'),
        ])
    )->toEqual($collection->values());
});

it('can return a sole data object', function () {
    $collection = new DataCollection(SimpleData::class, ['A', 'B']);

    $filtered = $collection->sole('string', '=', 'A');

    expect('A')
        ->toEqual($filtered->string);
});

it('can return a sole data object without specifying an operator', function () {
    $collection = new DataCollection(SimpleData::class, ['A', 'B']);

    $filtered = $collection->sole('string', 'A');

    expect('A')
        ->toEqual($filtered->string);
});

it('a collection can be merged', function () {
    $collectionA = SimpleData::collect(collect(['A', 'B']));
    $collectionB = SimpleData::collect(collect(['C', 'D']));

    $filtered = $collectionA->merge($collectionB)->toArray();

    expect([
        ['string' => 'A'],
        ['string' => 'B'],
        ['string' => 'C'],
        ['string' => 'D'],
    ])
        ->toMatchArray($filtered);
});

it('can serialize and unserialize a data collection', function () {
    $collection = new DataCollection(SimpleData::class, ['A', 'B']);

    $serialized = serialize($collection);

    assertMatchesSnapshot($serialized);

    $unserialized = unserialize($serialized);

    expect($unserialized)->toBeInstanceOf(DataCollection::class);
    expect($unserialized)->toEqual(new DataCollection(SimpleData::class, ['A', 'B']));
});

it('during the serialization process some properties are thrown away', function () {
    $collection = new DataCollection(SimpleData::class, ['A', 'B']);

    $collection->include('test');
    $collection->exclude('test');
    $collection->only('test');
    $collection->except('test');
    $collection->wrap('test');

    $unserialized = unserialize(serialize($collection));

    $invaded = invade($unserialized);

    expect($invaded->_dataContext)->toBeNull();
});

it('can use a custom collection extended from collection to collect a collection of data objects', function () {
    $collection = SimpleData::collect(new CustomCollection([
        ['string' => 'A'],
        ['string' => 'B'],
    ]));

    expect($collection)->toBeInstanceOf(CustomCollection::class);
    expect($collection[0])->toBeInstanceOf(SimpleData::class);
    expect($collection[1])->toBeInstanceOf(SimpleData::class);
});

it('does not mutate wrapped paginators during transformation', function () {
    $paginatorOfSimpleData = new Paginator([
        ['string' => 'A'],
        ['string' => 'B'],
    ], perPage: 15);

    $collection = SimpleData::collect($paginatorOfSimpleData, PaginatedDataCollection::class);
    $expect = [
        'data' => [
            ['string' => 'A'],
            ['string' => 'B'],
        ],
        'links' => [],
        'meta' => [
            'current_page' => 1,
            'first_page_url' => '/?page=1',
            'from' => 1,
            'next_page_url' => null,
            'path' => '/',
            'per_page' => 15,
            'prev_page_url' => null,
            'to' => 2,
        ],
    ];

    // Perform the transformation twice, the second should not throw
    expect($collection->toArray())->toBe($expect);
    expect($collection->toArray())->toBe($expect);
});
