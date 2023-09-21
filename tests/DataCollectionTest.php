<?php

use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Spatie\LaravelData\Concerns\DeprecatedData;
use Spatie\LaravelData\Contracts\DeprecatedData as DeprecatedDataContract;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Tests\Fakes\DataCollections\CustomDataCollection;
use Spatie\LaravelData\Tests\Fakes\DataCollections\CustomPaginatedDataCollection;
use Spatie\LaravelData\Tests\Fakes\LazyData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

use function Spatie\Snapshots\assertMatchesJsonSnapshot;
use function Spatie\Snapshots\assertMatchesSnapshot;

it('can get a paginated data collection', function () {
    $items = Collection::times(100, fn (int $index) => "Item {$index}");

    $paginator = new LengthAwarePaginator(
        $items->forPage(1, 15),
        100,
        15
    );

    $collection = new PaginatedDataCollection(SimpleData::class, $paginator);

    expect($collection)->toBeInstanceOf(PaginatedDataCollection::class);
    assertMatchesJsonSnapshot($collection->toJson());
});

it('can get a paginated cursor data collection', function () {
    $items = Collection::times(100, fn (int $index) => "Item {$index}");

    $paginator = new CursorPaginator(
        $items,
        15,
    );

    $collection = new CursorPaginatedDataCollection(SimpleData::class, $paginator);

    if (version_compare(app()->version(), '9.0.0', '<=')) {
        $this->markTestIncomplete('Laravel 8 uses a different format');
    }

    expect($collection)->toBeInstanceOf(CursorPaginatedDataCollection::class);
    assertMatchesJsonSnapshot($collection->toJson());
});

test('a collection can be constructed with data object', function () {
    $collectionA = new DataCollection(SimpleData::class, [
        SimpleData::from('A'),
        SimpleData::from('B'),
    ]);

    $collectionB = SimpleData::collect([
        'A',
        'B',
    ], DataCollection::class);

    expect($collectionB)->toArray()
        ->toMatchArray($collectionA->toArray());
});

test('a collection can be filtered', function () {
    $collection = new DataCollection(SimpleData::class, ['A', 'B']);

    $filtered = $collection->filter(fn (SimpleData $data) => $data->string === 'A')->toArray();

    expect([
        ['string' => 'A'],
    ])
        ->toMatchArray($filtered);
});

test('a collection can be transformed', function () {
    $collection = new DataCollection(SimpleData::class, ['A', 'B']);

    $filtered = $collection->through(fn (SimpleData $data) => new SimpleData("{$data->string}x"))->toArray();

    expect($filtered)->toMatchArray([
        ['string' => 'Ax'],
        ['string' => 'Bx'],
    ]);
});

test('a paginated collection can be transformed', function () {
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


it('can update data properties withing a collection', function () {
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

it('supports lazy collections', function () {
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

test('a collection can be transformed to JSON', function () {
    $collection = (new DataCollection(SimpleData::class, ['A', 'B', 'C']));

    expect('[{"string":"A"},{"string":"B"},{"string":"C"}]')
        ->toEqual($collection->toJson())
        ->toEqual(json_encode($collection));
});

it('will cast data object into the data collection objects', function () {
    $dataClass = new class ('') extends Data {
        public function __construct(public string $otherString)
        {
        }

        public static function fromSimpleData(SimpleData $simpleData): static
        {
            return new self($simpleData->string);
        }
    };

    $collection = new DataCollection($dataClass::class, [
        SimpleData::from('A'),
        SimpleData::from('B'),
    ]);

    expect($collection[0])
        ->toBeInstanceOf($dataClass::class)
        ->otherString->toEqual('A');

    expect($collection[1])
        ->toBeInstanceOf($dataClass::class)
        ->otherString->toEqual('B');
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

it('can use magical creation methods to create a collection', function () {
    $collection = new DataCollection(SimpleData::class, ['A', 'B']);

    expect($collection->toCollection()->all())
        ->toMatchArray([
            SimpleData::from('A'),
            SimpleData::from('B'),
        ]);
});

it('can return a custom data collection when collecting data', function () {
    $class = new class ('') extends Data implements DeprecatedDataContract {
        use DeprecatedData;

        protected static string $_collectionClass = CustomDataCollection::class;

        public function __construct(public string $string)
        {
        }
    };

    $collection = $class::collection([
        ['string' => 'A'],
        ['string' => 'B'],
    ]);

    expect($collection)->toBeInstanceOf(CustomDataCollection::class);
});

it('can return a custom paginated data collection when collecting data', function () {
    $class = new class ('') extends Data implements DeprecatedDataContract {
        use DeprecatedData;

        protected static string $_paginatedCollectionClass = CustomPaginatedDataCollection::class;

        public function __construct(public string $string)
        {
        }
    };

    $collection = $class::collection(new LengthAwarePaginator([['string' => 'A'], ['string' => 'B']], 2, 15));

    expect($collection)->toBeInstanceOf(CustomPaginatedDataCollection::class);
});

it(
    'can perform some collection operations',
    function (string $operation, array $arguments, array $expected) {
        $collection = new DataCollection(SimpleData::class, ['A', 'B', 'C']);

        $changedCollection = $collection->{$operation}(...$arguments);

        expect($changedCollection->toArray())
            ->toEqual($expected);
    }
)->with('collection-operations');

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

test('a collection can be merged', function () {
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
