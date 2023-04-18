<?php

use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\PartialTrees;
use Spatie\LaravelData\Tests\Fakes\DataCollections\CustomDataCollection;
use Spatie\LaravelData\Tests\Fakes\DataCollections\CustomPaginatedDataCollection;
use Spatie\LaravelData\Tests\Fakes\DefaultLazyData;
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

    $collection = SimpleData::collection($paginator);

    expect($collection)->toBeInstanceOf(PaginatedDataCollection::class);
    assertMatchesJsonSnapshot($collection->toJson());
});

it('can get a paginated cursor data collection', function () {
    $items = Collection::times(100, fn (int $index) => "Item {$index}");

    $paginator = new CursorPaginator(
        $items,
        15,
    );

    $collection = SimpleData::collection($paginator);

    if (version_compare(app()->version(), '9.0.0', '<=')) {
        $this->markTestIncomplete('Laravel 8 uses a different format');
    }

    expect($collection)->toBeInstanceOf(CursorPaginatedDataCollection::class);
    assertMatchesJsonSnapshot($collection->toJson());
});

test('a collection can be constructed with data object', function () {
    $collectionA = SimpleData::collection([
        SimpleData::from('A'),
        SimpleData::from('B'),
    ]);

    $collectionB = SimpleData::collection([
        'A',
        'B',
    ]);

    expect($collectionB)->toArray()
        ->toMatchArray($collectionA->toArray());
});

test('a collection can be filtered', function () {
    $collection = SimpleData::collection(['A', 'B']);

    $filtered = $collection->filter(fn (SimpleData $data) => $data->string === 'A')->toArray();

    expect([
        ['string' => 'A'],
    ])
        ->toMatchArray($filtered);
});

test('a collection can be transformed', function () {
    $collection = SimpleData::collection(['A', 'B']);

    $filtered = $collection->through(fn (SimpleData $data) => new SimpleData("{$data->string}x"))->toArray();

    expect($filtered)->toMatchArray([
        ['string' => 'Ax'],
        ['string' => 'Bx'],
    ]);
});

test('a paginated collection can be transformed', function () {
    $collection = SimpleData::collection(
        new LengthAwarePaginator(['A', 'B'], 2, 15)
    );

    $filtered = $collection->through(fn (SimpleData $data) => new SimpleData("{$data->string}x"))->toArray();

    expect($filtered['data'])->toMatchArray([
        ['string' => 'Ax'],
        ['string' => 'Bx'],
    ]);
});

it('is iteratable', function () {
    $collection = SimpleData::collection([
        'A', 'B', 'C', 'D',
    ]);

    $letters = [];

    foreach ($collection as $item) {
        $letters[] = $item->string;
    }

    expect($letters)->toMatchArray(['A', 'B', 'C', 'D']);
});

it('has array access', function (DataCollection $collection) {
    // Count
    expect($collection)->toHaveCount(4);

    // Offset exists
    expect($collection[3])->not->toBeEmpty();

    expect(empty($collection[5]))->toBeTrue();

    // Offset get
    expect(SimpleData::from('A'))->toEqual($collection[0]);

    expect(SimpleData::from('D'))->toEqual($collection[3]);

    if ($collection->items() instanceof AbstractPaginator || $collection->items() instanceof CursorPaginator) {
        return;
    }

    // Offset set
    $collection[2] = 'And now something completely different';
    $collection[4] = 'E';

    expect(
        SimpleData::from('And now something completely different')
    )
        ->toEqual($collection[2]);
    expect(SimpleData::from('E'))->toEqual($collection[4]);

    // Offset unset
    unset($collection[4]);

    expect($collection)->toHaveCount(4);
})->with('array-access-collections');

it('can dynamically include data based upon the request', function () {
    LazyData::$allowedIncludes = [''];

    $response = LazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request());

    expect($response)->getData(true)
        ->toMatchArray([
            [],
            [],
            [],
        ]);

    LazyData::$allowedIncludes = ['name'];

    $includedResponse = LazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request()->merge([
        'include' => 'name',
    ]));

    expect($includedResponse)->getData(true)
        ->toMatchArray([
            ['name' => 'Ruben'],
            ['name' => 'Freek'],
            ['name' => 'Brent'],
        ]);
});

it('can disabled manually including data in the request', function () {
    LazyData::$allowedIncludes = [];

    $response = LazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request()->merge([
        'include' => 'name',
    ]));

    expect($response)->getData(true)
        ->toMatchArray([
            [],
            [],
            [],
        ]);

    LazyData::$allowedIncludes = ['name'];

    $response = LazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request()->merge([
        'include' => 'name',
    ]));

    expect($response)->getData(true)
        ->toMatchArray([
            ['name' => 'Ruben'],
            ['name' => 'Freek'],
            ['name' => 'Brent'],
        ]);

    LazyData::$allowedIncludes = null;

    $response = LazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request()->merge([
        'include' => 'name',
    ]));

    expect($response)->getData(true)
        ->toMatchArray([
            ['name' => 'Ruben'],
            ['name' => 'Freek'],
            ['name' => 'Brent'],
        ]);
});

it('can dynamically exclude data based upon the request', function () {
    DefaultLazyData::$allowedExcludes = [];

    $response = DefaultLazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request());

    expect($response)->getData(true)
        ->toMatchArray([
            ['name' => 'Ruben'],
            ['name' => 'Freek'],
            ['name' => 'Brent'],
        ]);

    DefaultLazyData::$allowedExcludes = ['name'];

    $excludedResponse = DefaultLazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request()->merge([
        'exclude' => 'name',
    ]));

    expect($excludedResponse)->getData(true)
        ->toMatchArray([
            [],
            [],
            [],
        ]);
});

it('can disable manually excluding data in the request', function () {
    DefaultLazyData::$allowedExcludes = [];

    $response = DefaultLazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request()->merge([
        'exclude' => 'name',
    ]));

    expect($response)->getData(true)
        ->toMatchArray([
            ['name' => 'Ruben'],
            ['name' => 'Freek'],
            ['name' => 'Brent'],
        ]);

    DefaultLazyData::$allowedExcludes = ['name'];

    $response = DefaultLazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request()->merge([
        'exclude' => 'name',
    ]));

    expect($response)->getData(true)
        ->toMatchArray([
            [],
            [],
            [],
        ]);

    DefaultLazyData::$allowedExcludes = null;

    $response = DefaultLazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request()->merge([
        'exclude' => 'name',
    ]));

    expect($response)->getData(true)
        ->toMatchArray([
            [],
            [],
            [],
        ]);
});

it('can update data properties withing a collection', function () {
    $collection = LazyData::collection([
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

    $collection = SimpleData::collection($lazyCollection);

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
            SimpleData::collection(['A', 'B', 'C'])->toCollection()
        );
});

test('a collection can be transformed to JSON', function () {
    $collection = SimpleData::collection(['A', 'B', 'C']);

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

    $collection = $dataClass::collection([
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
    $collection = SimpleData::collection([
        1 => SimpleData::from('a'),
        3 => SimpleData::from('b'),
    ]);

    expect(
        SimpleData::collection([
            0 => SimpleData::from('a'),
            1 => SimpleData::from('b'),
        ])
    )->toEqual($collection->values());
});

it('can use magical creation methods to create a collection', function () {
    $collection = SimpleData::collection(['A', 'B']);

    expect($collection->toCollection()->all())
        ->toMatchArray([
            SimpleData::from('A'),
            SimpleData::from('B'),
        ]);
});

it('can return a custom data collection when collecting data', function () {
    $class = new class ('') extends Data {
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
    $class = new class ('') extends Data {
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
        $collection = SimpleData::collection(['A', 'B', 'C']);

        $changedCollection = $collection->{$operation}(...$arguments);

        expect($changedCollection->toArray())
            ->toEqual($expected);
    }
)->with('collection-operations');

it('can return a sole data object', function () {
    $collection = SimpleData::collection(['A', 'B']);

    $filtered = $collection->sole('string', '=', 'A');

    expect('A')
        ->toEqual($filtered->string);
});

it('can return a sole data object without specifying an operator', function () {
    $collection = SimpleData::collection(['A', 'B']);

    $filtered = $collection->sole('string', 'A');

    expect('A')
        ->toEqual($filtered->string);
});

test('a collection can be merged', function () {
    $collectionA = SimpleData::collection(['A', 'B']);
    $collectionB = SimpleData::collection(['C', 'D']);

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
    $collection = SimpleData::collection(['A', 'B']);

    $serialized = serialize($collection);

    assertMatchesSnapshot($serialized);

    $unserialized = unserialize($serialized);

    expect($unserialized)->toBeInstanceOf(DataCollection::class);
    expect($unserialized)->toEqual(SimpleData::collection(['A', 'B']));
});

it('during the serialization process some properties are thrown away', function () {
    $collection = SimpleData::collection(['A', 'B']);

    $collection->withPartialTrees(new PartialTrees());
    $collection->include('test');
    $collection->exclude('test');
    $collection->only('test');
    $collection->except('test');
    $collection->wrap('test');

    $unserialized = unserialize(serialize($collection));

    $invaded = invade($unserialized);

    expect($invaded->_partialTrees)->toBeNull();
    expect($invaded->_includes)->toBeEmpty();
    expect($invaded->_excludes)->toBeEmpty();
    expect($invaded->_only)->toBeEmpty();
    expect($invaded->_except)->toBeEmpty();
    expect($invaded->_wrap)->toBeNull();
});
