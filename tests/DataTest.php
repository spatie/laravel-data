<?php

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Inertia\LazyProp;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithCastable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Concerns\AppendableData;
use Spatie\LaravelData\Concerns\BaseData;
use Spatie\LaravelData\Concerns\ContextableData;
use Spatie\LaravelData\Concerns\DefaultableData;
use Spatie\LaravelData\Concerns\IncludeableData;
use Spatie\LaravelData\Concerns\ResponsableData;
use Spatie\LaravelData\Concerns\TransformableData;
use Spatie\LaravelData\Concerns\ValidateableData;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Concerns\WrappableData;
use Spatie\LaravelData\Contracts\DataObject;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\CannotCreateData;
use Spatie\LaravelData\Exceptions\CannotSetComputedValue;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Lazy\ClosureLazy;
use Spatie\LaravelData\Support\Lazy\InertiaLazy;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;
use Spatie\LaravelData\Tests\Fakes\Castables\SimpleCastable;
use Spatie\LaravelData\Tests\Fakes\Casts\ConfidentialDataCast;
use Spatie\LaravelData\Tests\Fakes\Casts\ConfidentialDataCollectionCast;
use Spatie\LaravelData\Tests\Fakes\Casts\ContextAwareCast;
use Spatie\LaravelData\Tests\Fakes\Casts\StringToUpperCast;
use Spatie\LaravelData\Tests\Fakes\CircData;
use Spatie\LaravelData\Tests\Fakes\DataWithMapper;
use Spatie\LaravelData\Tests\Fakes\DefaultLazyData;
use Spatie\LaravelData\Tests\Fakes\DummyDto;
use Spatie\LaravelData\Tests\Fakes\EnumData;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\ExceptData;
use Spatie\LaravelData\Tests\Fakes\FakeModelData;
use Spatie\LaravelData\Tests\Fakes\FakeNestedModelData;
use Spatie\LaravelData\Tests\Fakes\LazyData;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModel;
use Spatie\LaravelData\Tests\Fakes\Models\FakeNestedModel;
use Spatie\LaravelData\Tests\Fakes\MultiData;
use Spatie\LaravelData\Tests\Fakes\MultiLazyData;
use Spatie\LaravelData\Tests\Fakes\MultiNestedData;
use Spatie\LaravelData\Tests\Fakes\NestedData;
use Spatie\LaravelData\Tests\Fakes\NestedLazyData;
use Spatie\LaravelData\Tests\Fakes\OnlyData;
use Spatie\LaravelData\Tests\Fakes\PartialClassConditionalData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedProperty;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithoutConstructor;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithWrap;
use Spatie\LaravelData\Tests\Fakes\Transformers\ConfidentialDataCollectionTransformer;
use Spatie\LaravelData\Tests\Fakes\Transformers\ConfidentialDataTransformer;
use Spatie\LaravelData\Tests\Fakes\Transformers\StringToUpperTransformer;
use Spatie\LaravelData\Tests\Fakes\UlarData;
use Spatie\LaravelData\Tests\Fakes\UnionData;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\LaravelData\WithData;

use function Spatie\Snapshots\assertMatchesSnapshot;

it('can create a resource', function () {
    $data = new SimpleData('Ruben');

    expect($data->toArray())->toMatchArray([
        'string' => 'Ruben',
    ]);
});

it('can create a collection of resources', function () {
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

it('can include a lazy property', function () {
    $data = new LazyData(Lazy::create(fn () => 'test'));

    //    expect($data->toArray())->toBe([]);

    expect($data->include('name')->toArray())
        ->toMatchArray([
            'name' => 'test',
        ]);
});

it('can have a prefilled in lazy property', function () {
    $data = new LazyData('test');

    expect($data->toArray())->toMatchArray([
        'name' => 'test',
    ]);

    expect($data->include('name')->toArray())
        ->toMatchArray([
            'name' => 'test',
        ]);
});

it('can include a nested lazy property', function () {
    class TestIncludeableNestedLazyDataProperties extends Data
    {
        public function __construct(
            public LazyData|Lazy $data,
            #[DataCollectionOf(LazyData::class)]
            public array|Lazy $collection,
        ) {
        }
    }


    $data = new \TestIncludeableNestedLazyDataProperties(
        Lazy::create(fn () => LazyData::from('Hello')),
        Lazy::create(fn () => LazyData::collect(['is', 'it', 'me', 'your', 'looking', 'for',])),
    );

    expect((clone $data)->toArray())->toBe([]);

    expect((clone $data)->include('data')->toArray())
        ->toMatchArray([
            'data' => [],
        ]);

    expect((clone $data)->include('data.name')->toArray())
        ->toMatchArray([
            'data' => ['name' => 'Hello'],
        ]);

    expect((clone $data)->include('collection')->toArray())
        ->toMatchArray([
            'collection' => [
                [],
                [],
                [],
                [],
                [],
                [],
            ],
        ]);

    expect((clone $data)->include('collection.name')->toArray())
        ->toMatchArray([
            'collection' => [
                ['name' => 'is'],
                ['name' => 'it'],
                ['name' => 'me'],
                ['name' => 'your'],
                ['name' => 'looking'],
                ['name' => 'for'],
            ],
        ]);
});

it('can include specific nested data', function () {
    class TestSpecificDefinedIncludeableCollectedAndNestedLazyData extends Data
    {
        public function __construct(
            #[DataCollectionOf(MultiLazyData::class)]
            public array|Lazy $songs
        ) {
        }
    }

    $collection = Lazy::create(fn () => MultiLazyData::collect([
        DummyDto::rick(),
        DummyDto::bon(),
    ]));

    $data = new \TestSpecificDefinedIncludeableCollectedAndNestedLazyData($collection);

    expect($data->include('songs.name')->toArray())
        ->toMatchArray([
            'songs' => [
                ['name' => DummyDto::rick()->name],
                ['name' => DummyDto::bon()->name],
            ],
        ]);

    expect($data->include('songs.{name,artist}')->toArray())
        ->toMatchArray([
            'songs' => [
                [
                    'name' => DummyDto::rick()->name,
                    'artist' => DummyDto::rick()->artist,
                ],
                [
                    'name' => DummyDto::bon()->name,
                    'artist' => DummyDto::bon()->artist,
                ],
            ],
        ]);

    expect($data->include('songs.*')->toArray())
        ->toMatchArray([
            'songs' => [
                [
                    'name' => DummyDto::rick()->name,
                    'artist' => DummyDto::rick()->artist,
                    'year' => DummyDto::rick()->year,
                ],
                [
                    'name' => DummyDto::bon()->name,
                    'artist' => DummyDto::bon()->artist,
                    'year' => DummyDto::bon()->year,
                ],
            ],
        ]);
});

it('can have a conditional lazy data', function () {
    $blueprint = new class () extends Data {
        public function __construct(
            public string|Lazy|null $name = null
        ) {
        }

        public static function create(string $name): static
        {
            return new self(
                Lazy::when(fn () => $name === 'Ruben', fn () => $name)
            );
        }
    };

    $data = $blueprint::create('Freek');

    expect($data->toArray())->toBe([]);

    $data = $blueprint::create('Ruben');

    expect($data->toArray())->toMatchArray(['name' => 'Ruben']);
});

it('cannot have conditional lazy data manually loaded', function () {
    $blueprint = new class () extends Data {
        public function __construct(
            public string|Lazy|null $name = null
        ) {
        }

        public static function create(string $name): static
        {
            return new self(
                Lazy::when(fn () => $name === 'Ruben', fn () => $name)
            );
        }
    };

    $data = $blueprint::create('Freek');

    expect($data->include('name')->toArray())->toBeEmpty();
});

it('can include data based upon relations loaded', function () {
    $model = FakeNestedModel::factory()->create();

    $transformed = FakeNestedModelData::createWithLazyWhenLoaded($model)->all();

    expect($transformed)->not->toHaveKey('fake_model');

    $transformed = FakeNestedModelData::createWithLazyWhenLoaded($model->load('fakeModel'))->all();

    expect($transformed)->toHaveKey('fake_model')
        ->and($transformed['fake_model'])->toBeInstanceOf(FakeModelData::class);
});

it('can include data based upon relations loaded when they are null', function () {
    $model = FakeNestedModel::factory(['fake_model_id' => null])->create();

    $transformed = FakeNestedModelData::createWithLazyWhenLoaded($model)->all();

    expect($transformed)->not->toHaveKey('fake_model');

    $transformed = FakeNestedModelData::createWithLazyWhenLoaded($model->load('fakeModel'))->all();

    expect($transformed)->toHaveKey('fake_model')
        ->and($transformed['fake_model'])->toBeNull();
});

it('can have default included lazy data', function () {
    $data = new class ('Freek') extends Data {
        public function __construct(public string|Lazy $name)
        {
        }
    };

    expect($data->toArray())->toMatchArray(['name' => 'Freek']);
});

it('can exclude default lazy data', function () {
    $data = DefaultLazyData::from('Freek');

    expect($data->exclude('name')->toArray())->toBe([]);
});

it('always transforms lazy inertia data to inertia lazy props', function () {
    $blueprint = new class () extends Data {
        public function __construct(
            public string|InertiaLazy|null $name = null
        ) {
        }

        public static function create(string $name): static
        {
            return new self(
                Lazy::inertia(fn () => $name)
            );
        }
    };

    $data = $blueprint::create('Freek');

    expect($data->toArray()['name'])->toBeInstanceOf(LazyProp::class);
});

it('always transforms closure lazy into closures for inertia', function () {
    $blueprint = new class () extends Data {
        public function __construct(
            public string|ClosureLazy|null $name = null
        ) {
        }

        public static function create(string $name): static
        {
            return new self(
                Lazy::closure(fn () => $name)
            );
        }
    };

    $data = $blueprint::create('Freek');

    expect($data->toArray()['name'])->toBeInstanceOf(Closure::class);
});

it('can get the empty version of a data object', function () {
    $dataClass = new class () extends Data {
        public string $property;

        public string|Lazy $lazyProperty;

        public array $array;

        public Collection $collection;

        #[DataCollectionOf(SimpleData::class)]
        public DataCollection $dataCollection;

        public SimpleData $data;

        public Lazy|SimpleData $lazyData;

        public bool $defaultProperty = true;
    };

    expect($dataClass::empty())->toMatchArray([
        'property' => null,
        'lazyProperty' => null,
        'array' => [],
        'collection' => [],
        'dataCollection' => [],
        'data' => [
            'string' => null,
        ],
        'lazyData' => [
            'string' => null,
        ],
        'defaultProperty' => true,
    ]);
});

it('can overwrite properties in an empty version of a data object', function () {
    expect(SimpleData::empty())->toMatchArray([
        'string' => null,
    ]);

    expect(SimpleData::empty(['string' => 'Ruben']))->toMatchArray([
        'string' => 'Ruben',
    ]);
});

it('will use transformers to convert specific types', function () {
    $date = new DateTime('16 may 1994');

    $data = new class ($date) extends Data {
        public function __construct(public DateTime $date)
        {
        }
    };

    expect($data->toArray())->toMatchArray(['date' => '1994-05-16T00:00:00+00:00']);
});

it('can manually specific a transformer', function () {
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

it('can dynamically include data based upon the request', function () {
    LazyData::$allowedIncludes = [];

    $response = LazyData::from('Ruben')->toResponse(request());

    expect($response)->getData(true)->toBe([]);

    LazyData::$allowedIncludes = ['name'];

    $includedResponse = LazyData::from('Ruben')->toResponse(request()->merge([
        'include' => 'name',
    ]));

    expect($includedResponse)->getData(true)
        ->toMatchArray(['name' => 'Ruben']);
});

it('can disabled including data dynamically from the request', function () {
    LazyData::$allowedIncludes = [];

    $response = LazyData::from('Ruben')->toResponse(request()->merge([
        'include' => 'name',
    ]));

    expect($response->getData(true))->toBe([]);

    LazyData::$allowedIncludes = ['name'];

    $response = LazyData::from('Ruben')->toResponse(request()->merge([
        'include' => 'name',
    ]));

    expect($response->getData(true))->toBeArray(['name' => 'Ruben']);

    LazyData::$allowedIncludes = null;

    $response = LazyData::from('Ruben')->toResponse(request()->merge([
        'include' => 'name',
    ]));

    expect($response->getData(true))->toMatchArray(['name' => 'Ruben']);
});

it('can dynamically exclude data based upon the request', function () {
    DefaultLazyData::$allowedExcludes = [];

    $response = DefaultLazyData::from('Ruben')->toResponse(request());

    expect($response->getData(true))->toMatchArray(['name' => 'Ruben']);

    DefaultLazyData::$allowedExcludes = ['name'];

    $excludedResponse = DefaultLazyData::from('Ruben')->toResponse(request()->merge([
        'exclude' => 'name',
    ]));

    expect($excludedResponse->getData(true))->toBe([]);
});

it('can disabled excluding data dynamically from the request', function () {
    DefaultLazyData::$allowedExcludes = [];

    $response = DefaultLazyData::from('Ruben')->toResponse(request()->merge([
        'exclude' => 'name',
    ]));

    expect($response->getData(true))->toMatchArray(['name' => 'Ruben']);

    DefaultLazyData::$allowedExcludes = ['name'];

    $response = DefaultLazyData::from('Ruben')->toResponse(request()->merge([
        'exclude' => 'name',
    ]));

    expect($response->getData(true))->toBe([]);

    DefaultLazyData::$allowedExcludes = null;

    $response = DefaultLazyData::from('Ruben')->toResponse(request()->merge([
        'exclude' => 'name',
    ]));

    expect($response->getData(true))->toBe([]);
});

it('can disabled only data dynamically from the request', function () {
    OnlyData::$allowedOnly = [];

    $response = OnlyData::from([
        'first_name' => 'Ruben',
        'last_name' => 'Van Assche',
    ])->toResponse(request()->merge([
        'only' => 'first_name',
    ]));

    expect($response->getData(true))->toBe([
        'first_name' => 'Ruben',
        'last_name' => 'Van Assche',
    ]);

    OnlyData::$allowedOnly = ['first_name'];

    $response = OnlyData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche'])->toResponse(request()->merge([
        'only' => 'first_name',
    ]));

    expect($response->getData(true))->toMatchArray([
        'first_name' => 'Ruben',
    ]);

    OnlyData::$allowedOnly = null;

    $response = OnlyData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche'])->toResponse(request()->merge([
        'only' => 'first_name',
    ]));

    expect($response->getData(true))->toMatchArray([
        'first_name' => 'Ruben',
    ]);
});

it('can disabled except data dynamically from the request', function () {
    ExceptData::$allowedExcept = [];

    $response = ExceptData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche'])->toResponse(request()->merge([
        'except' => 'first_name',
    ]));

    expect($response->getData(true))->toMatchArray([
        'first_name' => 'Ruben',
        'last_name' => 'Van Assche',
    ]);

    ExceptData::$allowedExcept = ['first_name'];

    $response = ExceptData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche'])->toResponse(request()->merge([
        'except' => 'first_name',
    ]));

    expect($response->getData(true))->toMatchArray([
        'last_name' => 'Van Assche',
    ]);

    ExceptData::$allowedExcept = null;

    $response = ExceptData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche'])->toResponse(request()->merge([
        'except' => 'first_name',
    ]));

    expect($response->getData(true))->toMatchArray([
        'last_name' => 'Van Assche',
    ]);
});

it('can get the data object without transforming', function () {
    $data = new class (
        $dataObject = new SimpleData('Test'),
        $dataCollection = new DataCollection(SimpleData::class, ['A', 'B']),
        Lazy::create(fn () => new SimpleData('Lazy')),
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

    expect($data->include('lazy')->all())->toMatchArray([
        'data' => $dataObject,
        'dataCollection' => $dataCollection,
        'lazy' => (new SimpleData('Lazy')),
        'string' => 'Test',
        'transformable' => $transformable,
    ]);
});

it('can append data via method overwriting', function () {
    $data = new class ('Freek') extends Data {
        public function __construct(public string $name)
        {
        }

        public function with(): array
        {
            return ['alt_name' => "{$this->name} from Spatie"];
        }
    };

    expect($data->toArray())->toMatchArray([
        'name' => 'Freek',
        'alt_name' => 'Freek from Spatie',
    ]);
});

it('can append data via method overwriting with closures', function () {
    $data = new class ('Freek') extends Data {
        public function __construct(public string $name)
        {
        }

        public function with(): array
        {
            return [
                'alt_name' => static function (self $data) {
                    return $data->name.' from Spatie via closure';
                },
            ];
        }
    };

    expect($data->toArray())->toMatchArray([
        'name' => 'Freek',
        'alt_name' => 'Freek from Spatie via closure',
    ]);
});

test('when using additional method and with method additional method gets priority', function () {
    $data = new class ('Freek') extends Data {
        public function __construct(public string $name)
        {
        }

        public function with(): array
        {
            return [
                'alt_name' => static function (self $data) {
                    return $data->name.' from Spatie via closure';
                },
            ];
        }
    };

    expect($data->additional(['alt_name' => 'I m Freek from additional'])->toArray())->toMatchArray([
        'name' => 'Freek',
        'alt_name' => 'I m Freek from additional',
    ]);
});

it('can get the data object without mapping properties names', function () {
    $data = new class ('Freek') extends Data {
        public function __construct(
            #[MapOutputName('snake_name')]
            public string $camelName
        ) {
        }
    };

    expect($data)->transform(TransformationContextFactory::create()->mapPropertyNames(false))
        ->toMatchArray([
            'camelName' => 'Freek',
        ]);
});

it('can get the data object without mapping', function () {
    $data = new class ('Freek') extends Data {
        public function __construct(
            #[MapOutputName('snake_name')]
            public string $camelName
        ) {
        }
    };

    expect($data)->transform(TransformationContextFactory::create()->mapPropertyNames(false))
        ->toMatchArray([
            'camelName' => 'Freek',
        ]);
});

it('can get the data object with mapping properties by default', function () {
    $data = new class ('Freek') extends Data {
        public function __construct(
            #[MapOutputName('snake_name')]
            public string $camelName
        ) {
        }
    };
    expect($data->transform())->toMatchArray([
        'snake_name' => 'Freek',
    ]);
});

it('can get the data object with mapping properties names', function () {
    $data = new class ('Freek', 'Hello World') extends Data {
        public function __construct(
            #[MapOutputName('snake_name')]
            public string $camelName,
            public string $helloCamelName
        ) {
        }
    };

    expect($data->toArray())->toMatchArray([
        'snake_name' => 'Freek',
        'helloCamelName' => 'Hello World',
    ]);
});

it('can append data via method call', function () {
    $data = new class ('Freek') extends Data {
        public function __construct(public string $name)
        {
        }
    };

    $transformed = $data->additional([
        'company' => 'Spatie',
        'alt_name' => fn (Data $data) => "{$data->name} from Spatie",
    ])->toArray();

    expect($transformed)->toMatchArray([
        'name' => 'Freek',
        'company' => 'Spatie',
        'alt_name' => 'Freek from Spatie',
    ]);
});

it('can optionally create data', function () {
    expect(SimpleData::optional(null))->toBeNull();
    expect(new SimpleData('Hello world'))->toEqual(
        SimpleData::optional(['string' => 'Hello world'])
    );
});

it('can create a data model without constructor', function () {
    expect(SimpleDataWithoutConstructor::fromString('Hello'))
        ->toEqual(SimpleDataWithoutConstructor::from('Hello'));

    expect(SimpleDataWithoutConstructor::fromString('Hello'))
        ->toEqual(SimpleDataWithoutConstructor::from([
            'string' => 'Hello',
        ]));

    expect(
        new DataCollection(SimpleDataWithoutConstructor::class, [
            SimpleDataWithoutConstructor::fromString('Hello'),
            SimpleDataWithoutConstructor::fromString('World'),
        ])
    )
        ->toEqual(SimpleDataWithoutConstructor::collect(['Hello', 'World'], DataCollection::class));
});

it('can create a data object from a model', function () {
    DummyModel::migrate();

    $model = DummyModel::create([
        'string' => 'test',
        'boolean' => true,
        'date' => CarbonImmutable::create(2020, 05, 16, 12, 00, 00),
        'nullable_date' => null,
    ]);

    $dataClass = new class () extends Data {
        public string $string;

        public bool $boolean;

        public Carbon $date;

        public ?Carbon $nullable_date;
    };

    $data = $dataClass::from(DummyModel::findOrFail($model->id));

    expect($data)
        ->string->toEqual('test')
        ->boolean->toBeTrue()
        ->nullable_date->toBeNull()
        ->and(CarbonImmutable::create(2020, 05, 16, 12, 00, 00)->eq($data->date))->toBeTrue();
});

it('can create a data object from a stdClass object', function () {
    $object = (object) [
        'string' => 'test',
        'boolean' => true,
        'date' => CarbonImmutable::create(2020, 05, 16, 12, 00, 00),
        'nullable_date' => null,
    ];

    $dataClass = new class () extends Data {
        public string $string;

        public bool $boolean;

        public CarbonImmutable $date;

        public ?Carbon $nullable_date;
    };

    $data = $dataClass::from($object);

    expect($data)
        ->string->toEqual('test')
        ->boolean->toBeTrue()
        ->nullable_date->toBeNull()
        ->and(CarbonImmutable::create(2020, 05, 16, 12, 00, 00)->eq($data->date))->toBeTrue();
});

it('can add the WithData trait to a request', function () {
    $formRequest = new class () extends FormRequest {
        use WithData;

        public string $dataClass = SimpleData::class;
    };

    $formRequest->replace([
        'string' => 'Hello World',
    ]);

    $data = $formRequest->getData();

    expect($data)->toEqual(SimpleData::from('Hello World'));
});

it('can add the WithData trait to a model', function () {
    $model = new class () extends Model {
        use WithData;

        protected string $dataClass = SimpleData::class;
    };

    $model->fill([
        'string' => 'Hello World',
    ]);

    $data = $model->getData();

    expect($data)->toEqual(SimpleData::from('Hello World'));
});

it('can define the WithData trait data class by method', function () {
    $arrayable = new class () implements Arrayable {
        use WithData;

        public function toArray()
        {
            return [
                'string' => 'Hello World',
            ];
        }

        protected function dataClass(): string
        {
            return SimpleData::class;
        }
    };

    $data = $arrayable->getData();

    expect($data)->toEqual(SimpleData::from('Hello World'));
});

it('has support fro readonly properties', function () {
    $dataClass = new class ('') extends Data {
        public function __construct(
            public readonly string $string
        ) {
        }
    };

    $data = $dataClass::from(['string' => 'Hello world']);

    expect($data)->toBeInstanceOf($dataClass::class)
        ->and($data->string)->toEqual('Hello world');
});

it('has support for intersection types', function () {
    $collection = collect(['a', 'b', 'c']);

    $dataClass = new class () extends Data {
        public Arrayable & \Countable $intersection;
    };

    $data = $dataClass::from(['intersection' => $collection]);

    expect($data)->toBeInstanceOf($dataClass::class)
        ->and($data->intersection)->toEqual($collection);
});

it('can transform to JSON', function () {
    expect('{"string":"Hello"}')
        ->toEqual(SimpleData::from('Hello')->toJson())
        ->toEqual(json_encode(SimpleData::from('Hello')));
});

it(
    'can construct a data object with both constructor promoted and default properties',
    function () {
        $dataClass = new class ('') extends Data {
            public string $property;

            public function __construct(
                public string $promoted_property,
            ) {
            }
        };

        $data = $dataClass::from([
            'property' => 'A',
            'promoted_property' => 'B',
        ]);

        expect($data)
            ->property->toEqual('A')
            ->promoted_property->toEqual('B');
    }
);

it('can construct a data object with default values', function () {
    $dataClass = new class ('', '') extends Data {
        public string $property;

        public string $default_property = 'Hello';

        public function __construct(
            public string $promoted_property,
            public string $default_promoted_property = 'Hello Again',
        ) {
        }
    };

    $data = $dataClass::from([
        'property' => 'Test',
        'promoted_property' => 'Test Again',
    ]);

    expect($data)
        ->property->toEqual('Test')
        ->promoted_property->toEqual('Test Again')
        ->default_property->toEqual('Hello')
        ->default_promoted_property->toEqual('Hello Again');
});

it('can construct a data object with default values and overwrite them', function () {
    $dataClass = new class ('', '') extends Data {
        public string $property;

        public string $default_property = 'Hello';

        public function __construct(
            public string $promoted_property,
            public string $default_promoted_property = 'Hello Again',
        ) {
        }
    };

    $data = $dataClass::from([
        'property' => 'Test',
        'default_property' => 'Test',
        'promoted_property' => 'Test Again',
        'default_promoted_property' => 'Test Again',
    ]);

    expect($data)
        ->property->toEqual('Test')
        ->promoted_property->toEqual('Test Again')
        ->default_property->toEqual('Test')
        ->default_promoted_property->toEqual('Test Again');
});

it('can use a custom transformer', function () {
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

it('can cast data object and collections using a custom cast', function () {
    $dataWithDefaultCastsClass = new class () extends Data {
        public SimpleData $nestedData;

        #[DataCollectionOf(SimpleData::class)]
        public array $nestedDataCollection;
    };

    $dataWithCustomCastsClass = new class () extends Data {
        #[WithCast(ConfidentialDataCast::class)]
        public SimpleData $nestedData;

        #[WithCast(ConfidentialDataCollectionCast::class)]
        #[DataCollectionOf(SimpleData::class)]
        public array $nestedDataCollection;
    };

    $dataWithDefaultCasts = $dataWithDefaultCastsClass::from([
        'nestedData' => 'a secret',
        'nestedDataCollection' => ['another secret', 'yet another secret'],
    ]);

    $dataWithCustomCasts = $dataWithCustomCastsClass::from([
        'nestedData' => 'a secret',
        'nestedDataCollection' => ['another secret', 'yet another secret'],
    ]);

    expect($dataWithDefaultCasts)
        ->nestedData->toEqual(SimpleData::from('a secret'))
        ->and($dataWithDefaultCasts)
        ->nestedDataCollection->toEqual(SimpleData::collect(['another secret', 'yet another secret']));

    expect($dataWithCustomCasts)
        ->nestedData->toEqual(SimpleData::from('CONFIDENTIAL'))
        ->and($dataWithCustomCasts)
        ->nestedDataCollection->toEqual(SimpleData::collect(['CONFIDENTIAL', 'CONFIDENTIAL']));
});

it('can cast data object with a castable property using anonymous class', function () {
    $dataWithCastablePropertyClass = new class (new SimpleCastable('')) extends Data {
        public function __construct(
            #[WithCastable(SimpleCastable::class)]
            public SimpleCastable $castableData,
        ) {
        }
    };

    $dataWithCastableProperty = $dataWithCastablePropertyClass::from(['castableData' => 'HELLO WORLD']);

    expect($dataWithCastableProperty)
        ->castableData->toEqual(new SimpleCastable('HELLO WORLD'));
});

it('can cast built-in types with custom casts', function () {
    $dataClass = new class ('', '') extends Data {
        public function __construct(
            public string $without_cast,
            #[WithCast(StringToUpperCast::class)]
            public string $with_cast
        ) {
        }
    };

    $data = $dataClass::from([
        'without_cast' => 'Hello World',
        'with_cast' => 'Hello World',
    ]);

    expect($data)
        ->without_cast->toEqual('Hello World')
        ->with_cast->toEqual('HELLO WORLD');
});

it('continues value assignment after a false boolean', function () {
    $dataClass = new class () extends Data {
        public bool $false;

        public bool $true;

        public string $string;

        public Carbon $date;
    };

    $data = $dataClass::from([
        'false' => false,
        'true' => true,
        'string' => 'string',
        'date' => Carbon::create(2020, 05, 16, 12, 00, 00),
    ]);

    expect($data)
        ->false->toBeFalse()
        ->true->toBeTrue()
        ->string->toEqual('string')
        ->and(Carbon::create(2020, 05, 16, 12, 00, 00)->equalTo($data->date))->toBeTrue();
});

it('can create an partial data object', function () {
    $dataClass = new class ('', Optional::create(), Optional::create()) extends Data {
        public function __construct(
            public string $string,
            public string|Optional $undefinable_string,
            #[WithCast(StringToUpperCast::class)]
            public string|Optional $undefinable_string_with_cast,
        ) {
        }
    };

    $partialData = $dataClass::from([
        'string' => 'Hello World',
    ]);

    expect($partialData)
        ->string->toEqual('Hello World')
        ->undefinable_string->toEqual(Optional::create())
        ->undefinable_string_with_cast->toEqual(Optional::create());

    $fullData = $dataClass::from([
        'string' => 'Hello World',
        'undefinable_string' => 'Hello World',
        'undefinable_string_with_cast' => 'Hello World',
    ]);

    expect($fullData)
        ->string->toEqual('Hello World')
        ->undefinable_string->toEqual('Hello World')
        ->undefinable_string_with_cast->toEqual('HELLO WORLD');
});

it('can transform a partial object', function () {
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

it('will not include lazy optional values when transforming', function () {
    $data = new class ('Hello World', Lazy::create(fn () => Optional::make())) extends Data {
        public function __construct(
            public string $string,
            public string|Optional|Lazy $lazy_optional_string,
        ) {
        }
    };

    expect($data->toArray())->toMatchArray([
        'string' => 'Hello World',
    ]);
});

it('excludes optional values data', function () {
    $dataClass = new class () extends Data {
        public string|Optional $name;
    };

    $data = $dataClass::from([]);

    expect($data->toArray())->toBe([]);
});

it('includes value if not optional data', function () {
    $dataClass = new class () extends Data {
        public string|Optional $name;
    };

    $data = $dataClass::from([
        'name' => 'Freek',
    ]);

    expect($data->toArray())->toMatchArray([
        'name' => 'Freek',
    ]);
});

it('can map transformed property names', function () {
    $data = new SimpleDataWithMappedProperty('hello');
    $dataCollection = SimpleDataWithMappedProperty::collect([
        ['description' => 'never'],
        ['description' => 'gonna'],
        ['description' => 'give'],
        ['description' => 'you'],
        ['description' => 'up'],
    ]);

    $dataClass = new class ('hello', $data, $data, $dataCollection, $dataCollection) extends Data {
        public function __construct(
            #[MapOutputName('property')]
            public string $string,
            public SimpleDataWithMappedProperty $nested,
            #[MapOutputName('nested_other')]
            public SimpleDataWithMappedProperty $nested_renamed,
            #[DataCollectionOf(SimpleDataWithMappedProperty::class)]
            public array $nested_collection,
            #[
                MapOutputName('nested_other_collection'),
                DataCollectionOf(SimpleDataWithMappedProperty::class)
            ]
            public array $nested_renamed_collection,
        ) {
        }
    };

    expect($dataClass->toArray())->toMatchArray([
        'property' => 'hello',
        'nested' => [
            'description' => 'hello',
        ],
        'nested_other' => [
            'description' => 'hello',
        ],
        'nested_collection' => [
            ['description' => 'never'],
            ['description' => 'gonna'],
            ['description' => 'give'],
            ['description' => 'you'],
            ['description' => 'up'],
        ],
        'nested_other_collection' => [
            ['description' => 'never'],
            ['description' => 'gonna'],
            ['description' => 'give'],
            ['description' => 'you'],
            ['description' => 'up'],
        ],
    ]);
});

it('can map transformed properties from a complete class', function () {
    $data = DataWithMapper::from([
        'cased_property' => 'We are the knights who say, ni!',
        'data_cased_property' =>
            ['string' => 'Bring us a, shrubbery!'],
        'data_collection_cased_property' => [
            ['string' => 'One that looks nice!'],
            ['string' => 'But not too expensive!'],
        ],
    ]);

    expect($data->toArray())->toMatchArray([
        'cased_property' => 'We are the knights who say, ni!',
        'data_cased_property' =>
            ['string' => 'Bring us a, shrubbery!'],
        'data_collection_cased_property' => [
            ['string' => 'One that looks nice!'],
            ['string' => 'But not too expensive!'],
        ],
    ]);
});

it('can use context in casts based upon the properties of the data object', function () {
    $dataClass = new class () extends Data {
        public SimpleData $nested;

        public string $string;

        #[WithCast(ContextAwareCast::class)]
        public string $casted;
    };

    $data = $dataClass::from([
        'nested' => 'Hello',
        'string' => 'world',
        'casted' => 'json:',
    ]);

    expect($data)->casted
        ->toEqual('json:+{"nested":"Hello","string":"world","casted":"json:"}');
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

it('can magically create a data object', function () {
    $dataClass = new class ('', '') extends Data {
        public function __construct(
            public mixed $propertyA,
            public mixed $propertyB,
        ) {
        }

        public static function fromStringWithDefault(string $a, string $b = 'World')
        {
            return new self($a, $b);
        }

        public static function fromIntsWithDefault(int $a, int $b)
        {
            return new self($a, $b);
        }

        public static function fromSimpleDara(SimpleData $data)
        {
            return new self($data->string, $data->string);
        }

        public static function fromData(Data $data)
        {
            return new self('data', json_encode($data));
        }
    };

    expect($dataClass::from('Hello'))->toEqual(new $dataClass('Hello', 'World'))
        ->and($dataClass::from('Hello', 'World'))->toEqual(new $dataClass('Hello', 'World'))
        ->and($dataClass::from(42, 69))->toEqual(new $dataClass(42, 69))
        ->and($dataClass::from(SimpleData::from('Hello')))->toEqual(new $dataClass('Hello', 'Hello'))
        ->and($dataClass::from(new EnumData(DummyBackedEnum::FOO)))->toEqual(new $dataClass('data', '{"enum":"foo"}'));
});


it('can conditionally include', function () {
    expect(
        MultiLazyData::from(DummyDto::rick())->includeWhen('artist', false)->toArray()
    )->toBeEmpty();

    expect(
        MultiLazyData::from(DummyDto::rick())
            ->includeWhen('artist', true)
            ->toArray()
    )
        ->toMatchArray([
            'artist' => 'Rick Astley',
        ]);

    expect(
        MultiLazyData::from(DummyDto::rick())
            ->includeWhen('name', fn (MultiLazyData $data) => $data->artist->resolve() === 'Rick Astley')
            ->toArray()
    )
        ->toMatchArray([
            'name' => 'Never gonna give you up',
        ]);
});

it('can conditionally include nested', function () {
    $data = new class () extends Data {
        public NestedLazyData $nested;
    };

    $data->nested = NestedLazyData::from('Hello World');

    expect($data->toArray())->toMatchArray(['nested' => []]);

    expect($data->includeWhen('nested.simple', true)->toArray())
        ->toMatchArray([
            'nested' => ['simple' => ['string' => 'Hello World']],
        ]);
});

it('can conditionally include using class defaults', function () {
    PartialClassConditionalData::setDefinitions(includeDefinitions: [
        'string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::createLazy(enabled: false))
        ->toArray()
        ->toMatchArray(['enabled' => false]);

    expect(PartialClassConditionalData::createLazy(enabled: true))
        ->toArray()
        ->toMatchArray(['enabled' => true, 'string' => 'Hello World']);
});

it('can conditionally include using class defaults nested', function () {
    PartialClassConditionalData::setDefinitions(includeDefinitions: [
        'nested.string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::createLazy(enabled: true))
        ->toArray()
        ->toMatchArray(['enabled' => true, 'nested' => ['string' => 'Hello World']]);
});

it('can conditionally include using class defaults multiple', function () {
    PartialClassConditionalData::setDefinitions(includeDefinitions: [
        'nested.string' => fn (PartialClassConditionalData $data) => $data->enabled,
        'string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::createLazy(enabled: false))
        ->toArray()
        ->toMatchArray(['enabled' => false]);

    expect(PartialClassConditionalData::createLazy(enabled: true))
        ->toArray()
        ->toMatchArray([
            'enabled' => true,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);
});

it('can conditionally exclude', function () {
    $data = new MultiLazyData(
        Lazy::create(fn () => 'Rick Astley')->defaultIncluded(),
        Lazy::create(fn () => 'Never gonna give you up')->defaultIncluded(),
        1989
    );

    expect((clone $data)->exceptWhen('artist', false)->toArray())
        ->toMatchArray([
            'artist' => 'Rick Astley',
            'name' => 'Never gonna give you up',
            'year' => 1989,
        ]);

    expect((clone $data)->exceptWhen('artist', true)->toArray())
        ->toMatchArray([
            'name' => 'Never gonna give you up',
            'year' => 1989,
        ]);

    expect(
        (clone $data)
            ->exceptWhen('name', fn (MultiLazyData $data) => $data->artist->resolve() === 'Rick Astley')
            ->toArray()
    )
        ->toMatchArray([
            'artist' => 'Rick Astley',
            'year' => 1989,
        ]);
});

it('can conditionally exclude nested', function () {
    $data = new class () extends Data {
        public NestedLazyData $nested;
    };

    $data->nested = new NestedLazyData(Lazy::create(fn () => SimpleData::from('Hello World'))->defaultIncluded());

    expect($data->toArray())->toMatchArray([
        'nested' => ['simple' => ['string' => 'Hello World']],
    ]);

    expect($data->exceptWhen('nested.simple', true)->toArray())
        ->toMatchArray(['nested' => []]);
});

it('can conditionally exclude using class defaults', function () {
    PartialClassConditionalData::setDefinitions(excludeDefinitions: [
        'string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::createDefaultIncluded(enabled: false))
        ->toArray()
        ->toMatchArray([
            'enabled' => false,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);

    expect(PartialClassConditionalData::createDefaultIncluded(enabled: true))
        ->toArray()
        ->toMatchArray([
            'enabled' => true,
            'nested' => ['string' => 'Hello World'],
        ]);
});

it('can conditionally exclude using class defaults nested', function () {
    PartialClassConditionalData::setDefinitions(excludeDefinitions: [
        'nested.string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::createDefaultIncluded(enabled: false))
        ->toArray()
        ->toMatchArray([
            'enabled' => false,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);

    expect(PartialClassConditionalData::createDefaultIncluded(enabled: true))
        ->toArray()
        ->toMatchArray([
            'enabled' => true,
            'string' => 'Hello World',
        ]);
});

it('can conditionally exclude using multiple class defaults', function () {
    PartialClassConditionalData::setDefinitions(excludeDefinitions: [
        'string' => fn (PartialClassConditionalData $data) => $data->enabled,
        'nested.string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::createDefaultIncluded(enabled: false))
        ->toArray()
        ->toMatchArray([
            'enabled' => false,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);

    expect(PartialClassConditionalData::createDefaultIncluded(enabled: true))
        ->toArray()
        ->toMatchArray(['enabled' => true]);
});

it('can conditionally define only', function () {
    $data = new MultiData('Hello', 'World');

    expect(
        (clone $data)->onlyWhen('first', true)->toArray()
    )
        ->toMatchArray([
            'first' => 'Hello',
        ]);

    expect(
        (clone $data)->onlyWhen('first', false)->toArray()
    )
        ->toMatchArray([
            'first' => 'Hello',
            'second' => 'World',
        ]);

    expect(
        (clone $data)
            ->onlyWhen('second', fn (MultiData $data) => $data->second === 'World')
            ->toArray()
    )
        ->toMatchArray(['second' => 'World']);

    expect(
        (clone $data)
            ->onlyWhen('first', fn (MultiData $data) => $data->first === 'Hello')
            ->onlyWhen('second', fn (MultiData $data) => $data->second === 'World')
            ->toArray()
    )
        ->toMatchArray([
            'first' => 'Hello',
            'second' => 'World',
        ]);
});

it('can conditionally define only nested', function () {
    $data = new class () extends Data {
        public MultiData $nested;
    };

    $data->nested = new MultiData('Hello', 'World');

    expect(
        (clone $data)->onlyWhen('nested.first', true)->toArray()
    )->toMatchArray([
        'nested' => ['first' => 'Hello'],
    ]);

    expect(
        (clone $data)->onlyWhen('nested.{first, second}', true)->toArray()
    )->toMatchArray([
        'nested' => [
            'first' => 'Hello',
            'second' => 'World',
        ],
    ]);
});

it('can conditionally define only using class defaults', function () {
    PartialClassConditionalData::setDefinitions(onlyDefinitions: [
        'string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::create(enabled: false))
        ->toArray()
        ->toMatchArray([
            'enabled' => false,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);

    expect(PartialClassConditionalData::create(enabled: true))
        ->toArray()
        ->toMatchArray(['string' => 'Hello World']);
});

it('can conditionally define only using class defaults nested', function () {
    PartialClassConditionalData::setDefinitions(onlyDefinitions: [
        'nested.string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::create(enabled: false))
        ->toArray()
        ->toMatchArray([
            'enabled' => false,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);

    expect(PartialClassConditionalData::create(enabled: true))
        ->toArray()
        ->toMatchArray([
            'nested' => ['string' => 'Hello World'],
        ]);
});

it('can conditionally define only using multiple class defaults', function () {
    PartialClassConditionalData::setDefinitions(onlyDefinitions: [
        'string' => fn (PartialClassConditionalData $data) => $data->enabled,
        'nested.string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::create(enabled: false))
        ->toArray()
        ->toMatchArray([
            'enabled' => false,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);

    expect(PartialClassConditionalData::create(enabled: true))
        ->toArray()
        ->toMatchArray([
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);
});

it('can conditionally define except', function () {
    $data = new MultiData('Hello', 'World');

    expect((clone $data)->exceptWhen('first', true))
        ->toArray()
        ->toMatchArray(['second' => 'World']);

    expect((clone $data)->exceptWhen('first', false))
        ->toArray()
        ->toMatchArray([
            'first' => 'Hello',
            'second' => 'World',
        ]);

    expect(
        (clone $data)
            ->exceptWhen('second', fn (MultiData $data) => $data->second === 'World')
    )
        ->toArray()
        ->toMatchArray([
            'first' => 'Hello',
        ]);

    expect(
        (clone $data)
            ->exceptWhen('first', fn (MultiData $data) => $data->first === 'Hello')
            ->exceptWhen('second', fn (MultiData $data) => $data->second === 'World')
            ->toArray()
    )->toBeEmpty();
});

it('can conditionally define except nested', function () {
    $data = new class () extends Data {
        public MultiData $nested;
    };

    $data->nested = new MultiData('Hello', 'World');

    expect((clone $data)->exceptWhen('nested.first', true))
        ->toArray()
        ->toMatchArray(['nested' => ['second' => 'World']]);

    expect((clone $data)->exceptWhen('nested.{first, second}', true))
        ->toArray()
        ->toMatchArray(['nested' => []]);
});

it('can conditionally define except using class defaults', function () {
    PartialClassConditionalData::setDefinitions(exceptDefinitions: [
        'string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::create(enabled: false))
        ->toArray()
        ->toMatchArray([
            'enabled' => false,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);

    expect(PartialClassConditionalData::create(enabled: true))
        ->toArray()
        ->toMatchArray([
            'enabled' => true,
            'nested' => ['string' => 'Hello World'],
        ]);
});

it('can conditionally define except using class defaults nested', function () {
    PartialClassConditionalData::setDefinitions(exceptDefinitions: [
        'nested.string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::create(enabled: false))
        ->toArray()
        ->toMatchArray([
            'enabled' => false,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);

    expect(PartialClassConditionalData::create(enabled: true))
        ->toArray()
        ->toMatchArray([
            'enabled' => true,
            'string' => 'Hello World',
            'nested' => [],
        ]);
});

it('can conditionally define except using multiple class defaults', function () {
    PartialClassConditionalData::setDefinitions(exceptDefinitions: [
        'string' => fn (PartialClassConditionalData $data) => $data->enabled,
        'nested.string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::create(enabled: false))
        ->toArray()
        ->toMatchArray([
            'enabled' => false,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);

    expect(PartialClassConditionalData::create(enabled: true))
        ->toArray()
        ->toMatchArray([
            'enabled' => true,
            'nested' => [],
        ]);
});

test('only has precedence over except', function () {
    $data = new MultiData('Hello', 'World');

    expect(
        (clone $data)->onlyWhen('first', true)
            ->exceptWhen('first', true)
            ->toArray()
    )->toMatchArray(['second' => 'World']);

    expect(
        (clone $data)->exceptWhen('first', true)->onlyWhen('first', true)->toArray()
    )->toMatchArray(['second' => 'World']);
});

it('can perform only and except on array properties', function () {
    $data = new class ('Hello World', ['string' => 'Hello World', 'int' => 42]) extends Data {
        public function __construct(
            public string $string,
            public array $array
        ) {
        }
    };

    expect((clone $data)->only('string', 'array.int'))
        ->toArray()
        ->toMatchArray([
            'string' => 'Hello World',
            'array' => ['int' => 42],
        ]);

    expect((clone $data)->except('string', 'array.int'))
        ->toArray()
        ->toMatchArray([
            'array' => ['string' => 'Hello World'],
        ]);
});

it('can wrap data objects', function () {
    expect(
        SimpleData::from('Hello World')
            ->wrap('wrap')
            ->toResponse(\request())
            ->getData(true)
    )->toMatchArray(['wrap' => ['string' => 'Hello World']]);

    expect(
        SimpleData::collect(['Hello', 'World'], DataCollection::class)
            ->wrap('wrap')
            ->toResponse(\request())
            ->getData(true)
    )->toMatchArray([
        'wrap' => [
            ['string' => 'Hello'],
            ['string' => 'World'],
        ],
    ]);
});

it('can wrap data objects using a global default', function () {
    config()->set('data.wrap', 'wrap');

    expect(
        SimpleData::from('Hello World')
            ->toResponse(\request())->getData(true)
    )->toMatchArray(['wrap' => ['string' => 'Hello World']]);

    expect(
        SimpleData::from('Hello World')
            ->wrap('other-wrap')
            ->toResponse(\request())->getData(true)
    )
        ->toMatchArray(['other-wrap' => ['string' => 'Hello World']]);

    expect(
        SimpleData::from('Hello World')
            ->withoutWrapping()
            ->toResponse(\request())->getData(true)
    )
        ->toMatchArray(['string' => 'Hello World']);

    expect(
        SimpleData::collect(['Hello', 'World'], DataCollection::class)
            ->toResponse(\request())->getData(true)
    )
        ->toMatchArray([
            'wrap' => [
                ['string' => 'Hello'],
                ['string' => 'World'],
            ],
        ]);

    expect(
        SimpleData::from('Hello World')
            ->withoutWrapping()
            ->toResponse(\request())->getData(true)
    )
        ->toMatchArray(['string' => 'Hello World']);

    expect(
        (new DataCollection(SimpleData::class, ['Hello', 'World']))
            ->wrap('other-wrap')
            ->toResponse(\request())
            ->getData(true)
    )
        ->toMatchArray([
            'other-wrap' => [
                ['string' => 'Hello'],
                ['string' => 'World'],
            ],
        ]);

    expect(
        (new DataCollection(SimpleData::class, ['Hello', 'World']))
            ->withoutWrapping()
            ->toResponse(\request())->getData(true)
    )
        ->toMatchArray([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]);
});

it('can set a default wrap on a data object', function () {
    expect(
        SimpleDataWithWrap::from('Hello World')
            ->toResponse(\request())
            ->getData(true)
    )
        ->toMatchArray(['wrap' => ['string' => 'Hello World']]);

    expect(
        SimpleDataWithWrap::from('Hello World')
            ->wrap('other-wrap')
            ->toResponse(\request())->getData(true)
    )
        ->toMatchArray(['other-wrap' => ['string' => 'Hello World']]);

    expect(
        SimpleDataWithWrap::from('Hello World')
            ->withoutWrapping()
            ->toResponse(\request())->getData(true)
    )
        ->toMatchArray(['string' => 'Hello World']);
});

it('wraps additional data', function () {
    $dataClass = new class ('Hello World') extends Data {
        public function __construct(
            public string $string
        ) {
        }

        public function with(): array
        {
            return ['with' => 'this'];
        }
    };

    $data = $dataClass->additional(['additional' => 'this'])
        ->wrap('wrap')
        ->toResponse(\request())
        ->getData(true);

    expect($data)->toMatchArray([
        'wrap' => ['string' => 'Hello World'],
        'additional' => 'this',
        'with' => 'this',
    ]);
});

it('wraps complex data structures', function () {
    $data = new MultiNestedData(
        new NestedData(SimpleData::from('Hello')),
        [
            new NestedData(SimpleData::from('World')),
        ],
    );

    expect(
        $data->wrap('wrap')->toResponse(\request())->getData(true)
    )->toMatchArray([
        'wrap' => [
            'nested' => ['simple' => ['string' => 'Hello']],
            'nestedCollection' => [
                ['simple' => ['string' => 'World']],
            ],
        ],
    ]);
});

it('wraps complex data structures with a global', function () {
    config()->set('data.wrap', 'wrap');

    $data = new MultiNestedData(
        new NestedData(SimpleData::from('Hello')),
        [
            new NestedData(SimpleData::from('World')),
        ],
    );

    expect(
        $data->wrap('wrap')->toResponse(\request())->getData(true)
    )->toMatchArray([
        'wrap' => [
            'nested' => ['simple' => ['string' => 'Hello']],
            'nestedCollection' => [
                'wrap' => [
                    ['simple' => ['string' => 'World']],
                ],
            ],
        ],
    ]);
});

it('only wraps responses', function () {
    expect(
        SimpleData::from('Hello World')->wrap('wrap')
    )
        ->toArray()
        ->toMatchArray(['string' => 'Hello World']);

    expect(
        SimpleData::collect(['Hello', 'World'], DataCollection::class)->wrap('wrap')
    )
        ->toArray()
        ->toMatchArray([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]);
});

it('can use only when transforming', function (array $directive, array $expectedOnly) {
    $dataClass = new class () extends Data {
        public string $first;

        public string $second;

        public MultiData $nested;

        #[DataCollectionOf(MultiData::class)]
        public DataCollection $collection;
    };

    $data = $dataClass::from([
        'first' => 'A',
        'second' => 'B',
        'nested' => ['first' => 'C', 'second' => 'D'],
        'collection' => [
            ['first' => 'E', 'second' => 'F'],
            ['first' => 'G', 'second' => 'H'],
        ],
    ]);

    expect($data->only(...$directive))
        ->toArray()
        ->toMatchArray($expectedOnly);
})->with('only-inclusion');

it('can use except when transforming', function (
    array $directive,
    array $expectedOnly,
    array $expectedExcept
) {
    $dataClass = new class () extends Data {
        public string $first;

        public string $second;

        public MultiData $nested;

        #[DataCollectionOf(MultiData::class)]
        public DataCollection $collection;
    };

    $data = $dataClass::from([
        'first' => 'A',
        'second' => 'B',
        'nested' => ['first' => 'C', 'second' => 'D'],
        'collection' => [
            ['first' => 'E', 'second' => 'F'],
            ['first' => 'G', 'second' => 'H'],
        ],
    ]);

    expect($data->except(...$directive)->toArray())
        ->toEqual($expectedExcept);
})->with('only-inclusion');

it('can use a trait', function () {
    $data = new class ('') implements DataObject {
        use ResponsableData;
        use IncludeableData;
        use AppendableData;
        use ValidateableData;
        use WrappableData;
        use TransformableData;
        use BaseData;
        use \Spatie\LaravelData\Concerns\EmptyData;
        use ContextableData;
        use DefaultableData;

        public function __construct(public string $string)
        {
        }

        public static function fromString(string $string): static
        {
            return new self($string);
        }
    };

    expect($data::from('Hi')->toArray())->toMatchArray(['string' => 'Hi'])
        ->and($data::from(['string' => 'Hi']))->toEqual(new $data('Hi'))
        ->and($data::from('Hi'))->toEqual(new $data('Hi'));
});

it('supports conversion from multiple date formats', function () {
    $data = new class () extends Data {
        public function __construct(
            #[WithCast(DateTimeInterfaceCast::class, ['Y-m-d\TH:i:sP', 'Y-m-d H:i:s'])]
            public ?DateTime $date = null
        ) {
        }
    };

    expect($data::from(['date' => '2022-05-16T14:37:56+00:00']))->toArray()
        ->toMatchArray(['date' => '2022-05-16T14:37:56+00:00'])
        ->and($data::from(['date' => '2022-05-16 17:00:00']))->toArray()
        ->toMatchArray(['date' => '2022-05-16T17:00:00+00:00']);
});

it(
    'will throw a custom exception when a data constructor cannot be called due to missing component',
    function () {
        SimpleData::from([]);
    }
)->throws(CannotCreateData::class, 'the constructor requires 1 parameters');

it('can inherit properties from a base class', function () {
    $dataClass = new class ('') extends SimpleData {
        public int $int;
    };

    $data = $dataClass::from(['string' => 'Hi', 'int' => 42]);

    expect($data)
        ->string->toBe('Hi')
        ->int->toBe(42);
});

it('can have a circular dependency', function () {
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

it('can restructure payload', function () {
    $class = new class () extends Data {
        public function __construct(
            public string|null $name = null,
            public string|null $address = null,
        ) {
        }

        public static function prepareForPipeline(Collection $properties): Collection
        {
            $properties->put('address', $properties->only(['line_1', 'city', 'state', 'zipcode'])->join(','));

            return $properties;
        }
    };

    $instance = $class::from([
        'name' => 'Freek',
        'line_1' => '123 Sesame St',
        'city' => 'New York',
        'state' => 'NJ',
        'zipcode' => '10010',
    ]);

    expect($instance->toArray())->toMatchArray([
        'name' => 'Freek',
        'address' => '123 Sesame St,New York,NJ,10010',
    ]);
});


it('works with livewire', function () {
    $class = new class ('') extends Data {
        use WireableData;

        public function __construct(
            public string $name,
        ) {
        }
    };

    $data = $class::fromLivewire(['name' => 'Freek']);

    expect($data)->toEqual(new $class('Freek'));
});

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

it('during the serialization process some properties are thrown away', function () {
    $object = SimpleData::from('Hello world');

    $object->include('test');
    $object->exclude('test');
    $object->only('test');
    $object->except('test');
    $object->wrap('test');

    $unserialized = unserialize(serialize($object));

    $invaded = invade($unserialized);

    expect($invaded->_dataContext)->toBeNull();
});

// TODO: extend tests here
it('can use an array to store data', function () {
    $dataClass = new class (
        [LazyData::from('A'), LazyData::from('B')],
        collect([LazyData::from('A'), LazyData::from('B')]),
    ) extends Data {
        public function __construct(
            #[DataCollectionOf(SimpleData::class)]
            public array $array,
            #[DataCollectionOf(SimpleData::class)]
            public Collection $collection,
        ) {
        }
    };

    $d = $dataClass::from([
        'array' => ['A', 'B'],
        'collection' => ['A', 'B'],
    ]);

    expect($dataClass->include('array.name', 'collection.name')->toArray())->toBe([
        'array' => [
            ['name' => 'A'],
            ['name' => 'B'],
        ],
        'collection' => [
            ['name' => 'A'],
            ['name' => 'B'],
        ],
    ]);
});

it('can write collection logic in a class', function () {
    class TestSomeCustomCollection extends Collection
    {
        public function nameAll(): string
        {
            return $this->map(fn ($data) => $data->string)->join(', ');
        }
    }

    $dataClass = new class () extends Data {
        public string $string;

        public static function fromString(string $string): self
        {
            $s = new self();

            $s->string = $string;

            return $s;
        }

        public static function collectArray(array $items): \TestSomeCustomCollection
        {
            return new \TestSomeCustomCollection($items);
        }
    };

    expect($dataClass::collect(['a', 'b', 'c']))
        ->toBeInstanceOf(\TestSomeCustomCollection::class)
        ->all()->toEqual([
            $dataClass::from('a'),
            $dataClass::from('b'),
            $dataClass::from('c'),
        ]);
});

it('can fetch lazy union data', function () {
    $data = UnionData::from(1);

    expect($data->id)->toBe(1);
    expect($data->simple->string)->toBe('A');
    expect($data->dataCollection->toCollection()->pluck('string')->toArray())->toBe(['B', 'C']);
    expect($data->fakeModel->string)->toBe('lazy');
});

it('can fetch non-lazy union data', function () {
    $data = UnionData::from('A');

    expect($data->id)->toBe(1);
    expect($data->simple->string)->toBe('A');
    expect($data->dataCollection->toCollection()->pluck('string')->toArray())->toBe(['B', 'C']);
    expect($data->fakeModel->string)->toBe('non-lazy');
});

it('can set a default value for data object', function () {
    $dataObject = new class ('', '') extends Data {
        #[Min(10)]
        public string|Optional $full_name;

        public function __construct(
            public string $first_name,
            public string $last_name,
        ) {
            $this->full_name = "{$this->first_name} {$this->last_name}";
        }
    };

    expect($dataObject::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche']))
        ->first_name->toBe('Ruben')
        ->last_name->toBe('Van Assche')
        ->full_name->toBe('Ruben Van Assche');

    expect($dataObject::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche', 'full_name' => 'Ruben Versieck']))
        ->first_name->toBe('Ruben')
        ->last_name->toBe('Van Assche')
        ->full_name->toBe('Ruben Versieck');

    expect($dataObject::validateAndCreate(['first_name' => 'Ruben', 'last_name' => 'Van Assche']))
        ->first_name->toBe('Ruben')
        ->last_name->toBe('Van Assche')
        ->full_name->toBe('Ruben Van Assche');

    expect(fn () => $dataObject::validateAndCreate(['first_name' => 'Ruben', 'last_name' => 'Van Assche', 'full_name' => 'too short']))
        ->toThrow(ValidationException::class);
});

it('can have a computed value', function () {
    $dataObject = new class ('', '') extends Data {
        #[Computed]
        public string $full_name;

        public function __construct(
            public string $first_name,
            public string $last_name,
        ) {
            $this->full_name = "{$this->first_name} {$this->last_name}";
        }
    };

    expect($dataObject::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche']))
        ->first_name->toBe('Ruben')
        ->last_name->toBe('Van Assche')
        ->full_name->toBe('Ruben Van Assche');

    expect($dataObject::validateAndCreate(['first_name' => 'Ruben', 'last_name' => 'Van Assche']))
        ->first_name->toBe('Ruben')
        ->last_name->toBe('Van Assche')
        ->full_name->toBe('Ruben Van Assche');

    expect(fn () => $dataObject::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche', 'full_name' => 'Ruben Versieck']))
        ->toThrow(CannotSetComputedValue::class);
});
