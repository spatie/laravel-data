<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\assertDatabaseHas;

use Spatie\LaravelData\Contracts\PropertyMorphableData;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Tests\Fakes\AbstractData\AbstractDataA;
use Spatie\LaravelData\Tests\Fakes\AbstractData\AbstractDataB;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCasts;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithDefaultCasts;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithEncryptedCasts;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithJson;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithDefaultValue;

beforeEach(function () {
    DummyModelWithCasts::migrate();
    DummyModelWithJson::migrate();
});

it('can save a data object', function () {
    DummyModelWithCasts::create([
        'data' => new SimpleData('Test'),
    ]);

    assertDatabaseHas(DummyModelWithCasts::class, [
        'data' => json_encode(['string' => 'Test']),
    ]);
});

it('can save a data object as an array', function () {
    DummyModelWithCasts::create([
        'data' => ['string' => 'Test'],
    ]);

    assertDatabaseHas(DummyModelWithCasts::class, [
        'data' => json_encode(['string' => 'Test']),
    ]);
});

it('can load a data object', function () {
    DB::table('dummy_model_with_casts')->insert([
        'data' => json_encode(['string' => 'Test']),
    ]);

    /** @var \Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCasts $model */
    $model = DummyModelWithCasts::first();

    expect($model->data)->toEqual(new SimpleData('Test'));
});

it('can save a null as a value', function () {
    DummyModelWithCasts::create([
        'data' => null,
    ]);

    assertDatabaseHas(DummyModelWithCasts::class, [
        'data' => null,
    ]);
});

it('can load null as a value', function () {
    DB::table('dummy_model_with_casts')->insert([
        'data' => null,
    ]);

    /** @var \Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCasts $model */
    $model = DummyModelWithCasts::first();

    expect($model->data)->toBeNull();
});

it('loads a cast object when nullable argument used and value is null in database', function () {
    DB::table('dummy_model_with_casts')->insert([
        'data' => null,
    ]);

    /** @var \Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithDefaultCasts $model */
    $model = DummyModelWithDefaultCasts::first();

    expect($model->data)
        ->toBeInstanceOf(SimpleDataWithDefaultValue::class)
        ->string->toEqual('default');
});

it('can use an abstract data class with multiple children', function () {
    $abstractA = new AbstractDataA('A\A');
    $abstractB = new AbstractDataB('B\B');

    $modelId = DummyModelWithCasts::create([
        'abstract_data' => $abstractA,
    ])->id;

    $model = DummyModelWithCasts::find($modelId);

    expect($model->abstract_data)
        ->toBeInstanceOf(AbstractDataA::class)
        ->a->toBe('A\A');

    $model->abstract_data = $abstractB;
    $model->save();

    $model = DummyModelWithCasts::find($modelId);

    expect($model->abstract_data)
        ->toBeInstanceOf(AbstractDataB::class)
        ->b->toBe('B\B');
});

it('can use an abstract data class with morph map', function () {
    app(DataConfig::class)->enforceMorphMap([
        'a' => AbstractDataA::class,
    ]);

    $abstractA = new AbstractDataA('A\A');
    $abstractB = new AbstractDataB('B\B');

    $modelA = DummyModelWithCasts::create([
        'abstract_data' => $abstractA,
    ]);

    $modelB = DummyModelWithCasts::create([
        'abstract_data' => $abstractB,
    ]);

    expect(json_decode($modelA->getRawOriginal('abstract_data'))->type)->toBe('a');
    expect(json_decode($modelB->getRawOriginal('abstract_data'))->type)->toBe(AbstractDataB::class);

    $loadedMorphedModel = DummyModelWithCasts::find($modelA->id);

    expect($loadedMorphedModel->abstract_data)
        ->toBeInstanceOf(AbstractDataA::class)
        ->a->toBe('A\A');
});

it('can save an encrypted data object', function () {
    $model = DummyModelWithEncryptedCasts::create([
        'data' => new SimpleData('Test'),
    ]);

    try {
        Crypt::decryptString($model->getRawOriginal('data'));
        $isEncrypted = true;
    } catch (DecryptException $e) {
        $isEncrypted = false;
    }

    expect($isEncrypted)->toBeTrue();
});

it('can load an encrypted data object', function () {
    DummyModelWithEncryptedCasts::create([
        'data' => new SimpleData('Test'),
    ]);

    /** @var \Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCasts $model */
    $model = DummyModelWithEncryptedCasts::first();

    expect($model->data)->toEqual(new SimpleData('Test'));
});

it('can load and save an abstract defined data object', function () {
    $abstractA = new AbstractDataA('A\A');

    $modelId = DummyModelWithEncryptedCasts::create([
        'abstract_data' => $abstractA,
    ])->id;

    $model = DummyModelWithEncryptedCasts::find($modelId);

    expect($model->abstract_data)
        ->toBeInstanceOf(AbstractDataA::class)
        ->a->toBe('A\A');


    try {
        Crypt::decryptString($model->getRawOriginal('abstract_data'));
        $isEncrypted = true;
    } catch (DecryptException $e) {
        $isEncrypted = false;
    }

    expect($isEncrypted)->toBeTrue();
});

it('can load and save an abstract property-morphable data object', function () {
    abstract class TestCastAbstractPropertyMorphableData extends Data implements PropertyMorphableData
    {
        public function __construct(
            #[\Spatie\LaravelData\Attributes\PropertyForMorph]
            public DummyBackedEnum $variant
        ) {
        }

        public static function morph(array $properties): ?string
        {
            return match ($properties['variant'] ?? null) {
                DummyBackedEnum::FOO => TestCastPropertyMorphableDataFoo::class,
                default => null,
            };
        }
    }

    class TestCastPropertyMorphableDataFoo extends TestCastAbstractPropertyMorphableData
    {
        public function __construct(public string $a)
        {
            parent::__construct(DummyBackedEnum::FOO);
        }
    }

    $modelClass = new class () extends Model {
        protected $casts = [
            'data' => TestCastAbstractPropertyMorphableData::class,
        ];

        protected $table = 'dummy_model_with_casts';

        public $timestamps = false;
    };

    $abstractA = new TestCastPropertyMorphableDataFoo('foo');

    $modelId = $modelClass::create([
        'data' => $abstractA,
    ])->id;

    assertDatabaseHas($modelClass::class, [
        'data' => json_encode(['a' => 'foo', 'variant' => 'foo']),
    ]);

    $model = $modelClass::find($modelId);

    expect($model->data)
        ->toBeInstanceOf(TestCastPropertyMorphableDataFoo::class)
        ->a->toBe('foo')
        ->variant->toBe(DummyBackedEnum::FOO);
});

it('can correctly detect if the attribute is dirty', function () {
    $model = new DummyModelWithJson();
    // Set raw because we want to inverse the order of the keys
    $model->setRawAttributes(['data' => json_encode(['second' => 'Second', 'first' => 'First'])]);
    $model->save();

    $model->setRawAttributes(['data' => json_encode(['first' => 'First', 'second' => 'Second'])]);
    expect($model->getRawOriginal('data'))->toBe('{"second":"Second","first":"First"}')
        ->and($model->getAttributes()['data'])->toBe('{"first":"First","second":"Second"}')
        ->and($model->isDirty('data'))->toBeFalse();

    $model->data->first = 'First2';
    expect($model->isDirty('data'))->toBeTrue();
})->skip(fn () => version_compare(app()->version(), '12.18.0', '<'));

it('can correctly detect if the attribute is dirty with null values', function () {
    $model = new DummyModelWithJson();
    $model->save();

    $model->setRawAttributes(['data' => json_encode(['first' => 'First', 'second' => 'Second'])]);

    expect($model->getRawOriginal('data'))->toBe(null)
        ->and($model->getAttributes()['data'])->toBe('{"first":"First","second":"Second"}')
        ->and($model->isDirty('data'))->toBeTrue();
})->skip(fn () => version_compare(app()->version(), '12.18.0', '<'));
