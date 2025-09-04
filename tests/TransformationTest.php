<?php

use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Hidden;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\MaxTransformationDepthReached;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;
use Spatie\LaravelData\Tests\Fakes\CircData;
use Spatie\LaravelData\Tests\Fakes\EnumData;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\Transformers\ConfidentialDataCollectionTransformer;
use Spatie\LaravelData\Tests\Fakes\Transformers\ConfidentialDataTransformer;
use Spatie\LaravelData\Tests\Fakes\Transformers\StringToUpperTransformer;
use Spatie\LaravelData\Tests\Fakes\UlarData;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\LaravelData\Transformers\Transformer;

use function Spatie\Snapshots\assertMatchesJsonSnapshot;

it('can transform a data object', function () {
    $data = new SimpleData('Ruben');

    expect($data->toArray())->toMatchArray([
        'string' => 'Ruben',
    ]);
});

it('can transform a collection of data objects', function () {
    $collection = SimpleData::collect(collect([
        'Ruben',
        'Freek',
        'Brent',
    ]), DataCollection::class);

    expect($collection->toArray())
        ->toMatchArray([
            ['string' => 'Ruben'],
            ['string' => 'Freek'],
            ['string' => 'Brent'],
        ]);
});

it('will use global transformers to convert specific types', function () {
    $date = new DateTime('16 may 1994');

    $data = new class ($date) extends Data {
        public function __construct(public DateTime $date)
        {
        }
    };

    expect($data->toArray())->toMatchArray(['date' => '1994-05-16T00:00:00+00:00']);
});

it('can use a manually specified transformer', function () {
    $date = new DateTime('16 may 1994');

    $data = new class ($date) extends Data {
        public function __construct(
            #[WithTransformer(DateTimeInterfaceTransformer::class, 'd-m-Y')]
            public $date
        ) {
        }
    };

    expect($data->toArray())->toMatchArray(['date' => '16-05-1994']);
});

test('a transformer will never handle a null value', function () {
    $data = new class (null) extends Data {
        public function __construct(
            #[WithTransformer(DateTimeInterfaceTransformer::class, 'd-m-Y')]
            public $date
        ) {
        }
    };

    expect($data->toArray())->toMatchArray(['date' => null]);
});

it('can get the data object without transforming', function () {
    $lazyData = new SimpleData('Lazy');

    $data = new class (
        $dataObject = new SimpleData('Test'),
        $dataCollection = new DataCollection(SimpleData::class, ['A', 'B']),
        Lazy::create(fn () => $lazyData),
        'Test',
        $transformable = new DateTime('16 may 1994')
    ) extends Data {
        public function __construct(
            public SimpleData $data,
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection $dataCollection,
            public Lazy|Data $lazy,
            public string $string,
            public DateTime $transformable
        ) {
        }
    };

    expect($data->all())->toMatchArray([
        'data' => $dataObject,
        'dataCollection' => $dataCollection,
        'string' => 'Test',
        'transformable' => $transformable,
    ]);

    expect($data->include('lazy')->all())->toEqual([
        'data' => $dataObject,
        'dataCollection' => $dataCollection,
        'lazy' => $lazyData,
        'string' => 'Test',
        'transformable' => $transformable,
    ]);
});

it('can transform to JSON', function () {
    expect('{"string":"Hello"}')
        ->toEqual(SimpleData::from('Hello')->toJson())
        ->toEqual(json_encode(SimpleData::from('Hello')));
});

it('can use a custom transformer for a data object and/or data collectable', function () {
    $nestedData = new class (42, 'Hello World') extends Data {
        public function __construct(
            public int $integer,
            public string $string,
        ) {
        }
    };

    $nestedDataCollection = $nestedData::collect([
        ['integer' => 314, 'string' => 'pi'],
        ['integer' => '69', 'string' => 'Laravel after hours'],
    ]);

    $dataWithDefaultTransformers = new class ($nestedData, $nestedDataCollection) extends Data {
        public function __construct(
            public Data $nestedData,
            #[DataCollectionOf(SimpleData::class)]
            public array $nestedDataCollection,
        ) {
        }
    };

    $dataWithSpecificTransformers = new class ($nestedData, $nestedDataCollection) extends Data {
        public function __construct(
            #[WithTransformer(ConfidentialDataTransformer::class)]
            public Data $nestedData,
            #[
                WithTransformer(ConfidentialDataCollectionTransformer::class),
                DataCollectionOf(SimpleData::class)
            ]
            public array $nestedDataCollection,
        ) {
        }
    };

    expect($dataWithDefaultTransformers->toArray())
        ->toMatchArray([
            'nestedData' => ['integer' => 42, 'string' => 'Hello World'],
            'nestedDataCollection' => [
                ['integer' => 314, 'string' => 'pi'],
                ['integer' => '69', 'string' => 'Laravel after hours'],
            ],
        ]);

    expect($dataWithSpecificTransformers->toArray())
        ->toMatchArray([
            'nestedData' => ['integer' => 'CONFIDENTIAL', 'string' => 'CONFIDENTIAL'],
            'nestedDataCollection' => [
                ['integer' => 'CONFIDENTIAL', 'string' => 'CONFIDENTIAL'],
                ['integer' => 'CONFIDENTIAL', 'string' => 'CONFIDENTIAL'],
            ],
        ]);
});

it('can transform built it types with custom transformers', function () {
    $data = new class ('Hello World', 'Hello World') extends Data {
        public function __construct(
            public string $without_transformer,
            #[WithTransformer(StringToUpperTransformer::class)]
            public string $with_transformer
        ) {
        }
    };

    expect($data->toArray())->toMatchArray([
        'without_transformer' => 'Hello World',
        'with_transformer' => 'HELLO WORLD',
    ]);
});

it('will not transform optional values', function () {
    $dataClass = new class ('', Optional::create(), Optional::create()) extends Data {
        public function __construct(
            public string $string,
            public string|Optional $undefinable_string,
            #[WithTransformer(StringToUpperTransformer::class)]
            public string|Optional $undefinable_string_with_transformer,
        ) {
        }
    };

    $partialData = $dataClass::from([
        'string' => 'Hello World',
    ]);

    $fullData = $dataClass::from([
        'string' => 'Hello World',
        'undefinable_string' => 'Hello World',
        'undefinable_string_with_transformer' => 'Hello World',
    ]);

    expect($partialData->toArray())->toMatchArray([
        'string' => 'Hello World',
    ]);

    expect($fullData->toArray())->toMatchArray([
        'string' => 'Hello World',
        'undefinable_string' => 'Hello World',
        'undefinable_string_with_transformer' => 'HELLO WORLD',
    ]);
});

it('will transform native enums', function () {
    $data = EnumData::from([
        'enum' => DummyBackedEnum::FOO,
    ]);

    expect($data->toArray())->toMatchArray([
        'enum' => 'foo',
    ])
        ->and($data->all())->toMatchArray([
            'enum' => DummyBackedEnum::FOO,
        ]);
});

it('can have a circular dependency which will not go into an infinite loop', function () {
    $data = CircData::from([
        'string' => 'Hello World',
        'ular' => [
            'string' => 'Hello World',
            'circ' => [
                'string' => 'Hello World',
            ],
        ],
    ]);

    expect($data)->toEqual(
        new CircData('Hello World', new UlarData('Hello World', new CircData('Hello World', null)))
    );

    expect($data->toArray())->toMatchArray([
        'string' => 'Hello World',
        'ular' => [
            'string' => 'Hello World',
            'circ' => [
                'string' => 'Hello World',
                'ular' => null,
            ],
        ],
    ]);
});

it('can have a hidden value', function () {
    $dataObject = new class ('', '') extends Data {
        public function __construct(
            public string $show,
            #[Hidden]
            public string $hidden,
        ) {
        }
    };

    expect($dataObject::from(['show' => 'Yes', 'hidden' => 'No']))
        ->show->toBe('Yes')
        ->hidden->toBe('No');

    expect($dataObject::validateAndCreate(['show' => 'Yes', 'hidden' => 'No']))
        ->show->toBe('Yes')
        ->hidden->toBe('No');

    expect($dataObject::from(['show' => 'Yes', 'hidden' => 'No'])->toArray())->toBe(['show' => 'Yes']);
});

it('is possible to add extra global transformers when transforming using context', function () {
    $dataClass = new class () extends Data {
        public DateTime $dateTime;
    };

    $data = $dataClass::from([
        'dateTime' => new DateTime(),
    ]);

    $customTransformer = new class () implements Transformer {
        public function transform(DataProperty $property, mixed $value, TransformationContext $context): string
        {
            return "Custom transformed date";
        }
    };

    $transformed = $data->transform(
        TransformationContextFactory::create()->withTransformer(DateTimeInterface::class, $customTransformer)
    );

    expect($transformed)->toBe([
        'dateTime' => 'Custom transformed date',
    ]);
});

it('can transform a paginated data collection', function () {
    $items = Collection::times(100, fn (int $index) => "Item {$index}");

    $paginator = new LengthAwarePaginator(
        $items->forPage(1, 15),
        100,
        15
    );

    $collection = new PaginatedDataCollection(SimpleData::class, $paginator);

    expect($collection)->toBeInstanceOf(PaginatedDataCollection::class);

    $output = $collection->toArray();

    expect($output)->toHaveKeys(['data', 'links', 'meta']);

    expect($output['data'])->toHaveCount(15);
    expect($output['data'][0])->toBe(['string' => 'Item 1']);
    expect($output['data'][14])->toBe(['string' => 'Item 15']);

    expect($output['links'])->toHaveCount(9);

    expect($output['meta'])
        ->toHaveKey('current_page', 1)
        ->toHaveKey('first_page_url', '/?page=1')
        ->toHaveKey('from', 1)
        ->toHaveKey('last_page', 7)
        ->toHaveKey('last_page_url', '/?page=7')
        ->toHaveKey('next_page_url', '/?page=2')
        ->toHaveKey('path', '/')
        ->toHaveKey('per_page', 15)
        ->toHaveKey('prev_page_url', null)
        ->toHaveKey('to', 15)
        ->toHaveKey('total', 100);
});

it('can transform a paginated cursor data collection', function () {
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

it('can transform a data collection', function () {
    $collection = new DataCollection(SimpleData::class, ['A', 'B']);

    $filtered = $collection->through(fn (SimpleData $data) => new SimpleData("{$data->string}x"))->toArray();

    expect($filtered)->toMatchArray([
        ['string' => 'Ax'],
        ['string' => 'Bx'],
    ]);
});

it('can transform a data collection into JSON', function () {
    $collection = (new DataCollection(SimpleData::class, ['A', 'B', 'C']));

    expect('[{"string":"A"},{"string":"B"},{"string":"C"}]')
        ->toEqual($collection->toJson())
        ->toEqual(json_encode($collection));
});

it('can transform a typed iterable with a custom transformer', function () {
    $dataClass = new class () extends Data {
        /** @var array<string> */
        public array $array;
    };

    $transformed = $dataClass::from(['array' => ['a', 'b', 'c']])->transform(
        TransformationContextFactory::create()
            ->withTransformer('string', StringToUpperTransformer::class)
    );

    expect($transformed)->toBe(['array' => ['A', 'B', 'C']]);
})->skip(fn () => config('data.features.cast_and_transform_iterables') === false);

it('does not transform a typed iterable with a custom transformer when a union type is used with a non-iterable value', function () {
    $dataClass = new class () extends Data {
        /** @var string|array<string> */
        public string|array $array;
    };

    $transformed = $dataClass::from(['array' => 'a'])->transform(
        TransformationContextFactory::create()
            ->withTransformer('string', StringToUpperTransformer::class)
    );

    expect($transformed)->toBe(['array' => 'A']);
})->skip(fn () => config('data.features.cast_and_transform_iterables') === false);


it('it possible to set the max transformation depth when transforming objects', function () {
    $a = new stdClass();
    $b = new stdClass();

    $a->b = $b;
    $b->a = $a;

    class TestMaxDataObjectTransformationDepthA extends Data
    {
        public function __construct(
            public Lazy|TestMaxDataObjectTransformationDepthB $dataB
        ) {
            $this->includePermanently('dataB');
        }

        public static function fromOther(stdClass $b): self
        {
            return new self(Lazy::create(fn () => TestMaxDataObjectTransformationDepthB::from($b->a)));
        }
    }

    class TestMaxDataObjectTransformationDepthB extends Data
    {
        public function __construct(
            public Lazy|TestMaxDataObjectTransformationDepthA $dataA
        ) {
            $this->includePermanently('dataA');
        }

        public static function fromOther(stdClass $a): self
        {
            return new self(Lazy::create(fn () => TestMaxDataObjectTransformationDepthA::from($a->b)));
        }
    }

    expect(fn () => TestMaxDataObjectTransformationDepthB::fromOther($a)->transform(
        TransformationContextFactory::create()->maxDepth(4)
    ))->toThrow(MaxTransformationDepthReached::class);

    expect(TestMaxDataObjectTransformationDepthB::fromOther($a)->transform(
        TransformationContextFactory::create()->maxDepth(4, throw: false)
    ))->toBe([
        'dataA' => [
            'dataB' => [
                'dataA' => [
                    'dataB' => [],
                ],
            ],
        ],
    ]);
});

it('it possible to set the max transformation depth when transforming collections', function () {
    $a = new stdClass();
    $b = new stdClass();

    $a->b = $b;
    $b->a = $a;

    class TestMaxDatCollectionTransformationDepthA extends Data
    {
        public function __construct(
            #[DataCollectionOf(TestMaxDatCollectionTransformationDepthB::class)]
            public Lazy|DataCollection $ca
        ) {
            $this->includePermanently('ca');
        }

        public static function fromOther(stdClass $b): self
        {
            return new self(Lazy::create(fn () => TestMaxDatCollectionTransformationDepthB::collect([$b->a])));
        }
    }

    class TestMaxDatCollectionTransformationDepthB extends Data
    {
        public function __construct(
            #[DataCollectionOf(TestMaxDatCollectionTransformationDepthA::class)]
            public Lazy|DataCollection $cb
        ) {
            $this->includePermanently('cb');
        }

        public static function fromOther(stdClass $a): self
        {
            return new self(Lazy::create(fn () => TestMaxDatCollectionTransformationDepthA::collect([$a->b])));
        }
    }

    expect(fn () => TestMaxDatCollectionTransformationDepthB::fromOther($a)->transform(
        TransformationContextFactory::create()->maxDepth(4)
    ))->toThrow(MaxTransformationDepthReached::class);

    expect(TestMaxDatCollectionTransformationDepthB::fromOther($a)->transform(
        TransformationContextFactory::create()->maxDepth(4, throw: false)
    ))->toBe([
        'cb' => [
            [
                'ca' => [
                    [
                        'cb' => [
                            ['ca' => []],
                        ],
                    ],
                ],
            ],
        ],
    ]);
});
