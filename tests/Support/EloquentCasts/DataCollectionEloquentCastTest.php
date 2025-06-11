<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\assertDatabaseHas;

use Spatie\LaravelData\Contracts\PropertyMorphableData;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Tests\Fakes\AbstractData\AbstractData;
use Spatie\LaravelData\Tests\Fakes\AbstractData\AbstractDataA;
use Spatie\LaravelData\Tests\Fakes\AbstractData\AbstractDataB;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCasts;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCustomCollectionCasts;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithDefaultCasts;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithJson;
use Spatie\LaravelData\Tests\Fakes\MultiData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataCollection;

beforeEach(function () {
    DummyModelWithCasts::migrate();
    DummyModelWithJson::migrate();
});

it('can save a data collection', function () {
    DummyModelWithCasts::create([
        'data_collection' => SimpleData::collect([
            'Hello',
            'World',
        ], DataCollection::class),
    ]);

    assertDatabaseHas(DummyModelWithCasts::class, [
        'data_collection' => json_encode([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]),
    ]);
});

it('can save a data object as an array', function () {
    DummyModelWithCasts::create([
        'data_collection' => [
            ['string' => 'Hello'],
            ['string' => 'World'],
        ],
    ]);

    assertDatabaseHas(DummyModelWithCasts::class, [
        'data_collection' => json_encode([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]),
    ]);
});

it('can save a data object as an array from a collection', function () {
    DummyModelWithCasts::create([
        'data_collection' => collect([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]),
    ]);

    assertDatabaseHas(DummyModelWithCasts::class, [
        'data_collection' => json_encode([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]),
    ]);
});

it('can load a data object', function () {
    DB::table('dummy_model_with_casts')->insert([
        'data_collection' => json_encode([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]),
    ]);

    /** @var \Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCasts $model */
    $model = DummyModelWithCasts::first();

    expect($model->data_collection)->toEqual(new DataCollection(SimpleData::class, [
        new SimpleData('Hello'),
        new SimpleData('World'),
    ]));
});

it('can save a null as a value', function () {
    DummyModelWithCasts::create([
        'data_collection' => null,
    ]);

    assertDatabaseHas(DummyModelWithCasts::class, [
        'data_collection' => null,
    ]);
});

it('can load null as a value', function () {
    DB::table('dummy_model_with_casts')->insert([
        'data_collection' => null,
    ]);

    /** @var \Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCasts $model */
    $model = DummyModelWithCasts::first();

    expect($model->data_collection)->toBeNull();
});

it('can save a custom data collection', function () {
    DummyModelWithCustomCollectionCasts::create([
        'data_collection' => [
            ['string' => 'Hello'],
            ['string' => 'World'],
        ],
    ]);

    assertDatabaseHas(DummyModelWithCustomCollectionCasts::class, [
        'data_collection' => json_encode([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ], JSON_PRETTY_PRINT),
    ]);
});

it('retrieves custom data collection', function () {
    DB::table('dummy_model_with_casts')->insert([
        'data_collection' => json_encode([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]),
    ]);

    /** @var \Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCustomCollectionCasts $model */
    $model = DummyModelWithCustomCollectionCasts::first();

    expect($model->data_collection)->toEqual(new SimpleDataCollection(
        SimpleData::class,
        [
            new SimpleData('Hello'),
            new SimpleData('World'),
        ]
    ));
});

it('loads a custom data collection when nullable argument used and value is null in database', function () {
    DB::table('dummy_model_with_casts')->insert([
        'data' => null,
    ]);

    /** @var \Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithDefaultCasts $model */
    $model = DummyModelWithDefaultCasts::first();

    expect($model->data_collection)
        ->toBeInstanceOf(SimpleDataCollection::class)
        ->toBeEmpty();
});

it('can use an abstract data collection with multiple children', function () {
    $abstractA = new AbstractDataA('A\A');
    $abstractB = new AbstractDataB('B\B');

    $modelId = DummyModelWithCasts::create([
        'abstract_collection' => [$abstractA, $abstractB],
    ])->id;

    $model = DummyModelWithCasts::find($modelId);

    expect($model->abstract_collection)
        ->toBeInstanceOf(DataCollection::class)
        ->each->toBeInstanceOf(AbstractData::class);

    expect($model->abstract_collection[0])->toBeInstanceOf(AbstractDataA::class);
    expect($model->abstract_collection[1])->toBeInstanceOf(AbstractDataB::class);
});

it('can load and save an abstract property-morphable data collection', function () {
    abstract class TestCollectionCastAbstractPropertyMorphableData extends Data implements PropertyMorphableData
    {
        public function __construct(
            #[\Spatie\LaravelData\Attributes\PropertyForMorph]
            public string $variant
        ) {
        }

        public static function morph(array $properties): ?string
        {
            return match ($properties['variant'] ?? null) {
                'a' => TestCollectionCastPropertyMorphableDataA::class,
                'b' => TestCollectionCastPropertyMorphableDataB::class,
                default => null,
            };
        }
    }

    class TestCollectionCastPropertyMorphableDataA extends TestCollectionCastAbstractPropertyMorphableData
    {
        public function __construct(public string $a, public DummyBackedEnum $enum)
        {
            parent::__construct('a');
        }
    }

    class TestCollectionCastPropertyMorphableDataB extends TestCollectionCastAbstractPropertyMorphableData
    {
        public function __construct(public string $b)
        {
            parent::__construct('b');
        }
    }

    $modelClass = new class () extends Model {
        protected $casts = [
            'data_collection' => SimpleDataCollection::class.':'.TestCollectionCastAbstractPropertyMorphableData::class,
        ];

        protected $table = 'dummy_model_with_casts';

        public $timestamps = false;
    };

    $abstractA = new TestCollectionCastPropertyMorphableDataA('foo', DummyBackedEnum::FOO);
    $abstractB = new TestCollectionCastPropertyMorphableDataB('bar');

    $modelId = $modelClass::create([
        'data_collection' => [$abstractA, $abstractB],
    ])->id;

    assertDatabaseHas($modelClass::class, [
        'data_collection' => json_encode([
            ['a' => 'foo', 'enum' => 'foo', 'variant' => 'a'],
            ['b' => 'bar', 'variant' => 'b'],
        ], JSON_PRETTY_PRINT),
    ]);

    $model = $modelClass::find($modelId);

    expect($model->data_collection[0])
        ->toBeInstanceOf(TestCollectionCastPropertyMorphableDataA::class)
        ->a->toBe('foo')
        ->enum->toBe(DummyBackedEnum::FOO);

    expect($model->data_collection[1])
        ->toBeInstanceOf(TestCollectionCastPropertyMorphableDataB::class)
        ->b->toBe('bar');
});


it('can correctly detect if the attribute is dirty', function () {

    // Set a raw JSON string with spaces in it to mimic database behavior
    $model = new DummyModelWithJson();
    $model->setRawAttributes(['data_collection' => '[{"second": "Second", "first": "First"}, {"first": "Third", "second": "Fourth"}]']);
    $model->save();

    $model->data_collection = [
        0 => new MultiData('First', 'Second'),
        1 => new MultiData('Third', 'Fourth'),
    ];

    expect($model->getRawOriginal('data_collection'))->toBe('[{"second": "Second", "first": "First"}, {"first": "Third", "second": "Fourth"}]')
        ->and($model->getAttributes()['data_collection'])->toBe('[{"first":"First","second":"Second"},{"first":"Third","second":"Fourth"}]')
        ->and($model->isDirty('data_collection'))->toBeFalse();
});
