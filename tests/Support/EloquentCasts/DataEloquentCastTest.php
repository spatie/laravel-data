<?php

use Illuminate\Support\Facades\DB;

use function Pest\Laravel\assertDatabaseHas;

use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Tests\Fakes\AbstractData\AbstractDataA;
use Spatie\LaravelData\Tests\Fakes\AbstractData\AbstractDataB;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnumWithOptions;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCasts;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithDefaultCasts;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithEloquentExcludedCasts;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithDefaultValue;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithEloquentExcludedTransformerData;
use Spatie\LaravelData\Tests\Fakes\Transformers\OptionsTransformer;
use Spatie\LaravelData\Transformers\EnumTransformer;

beforeEach(function () {
    DummyModelWithCasts::migrate();
    DummyModelWithEloquentExcludedCasts::migrate();
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

it('skips eloquent-excluded transformers during casting', function () {
    resolve(DataConfig::class)->setTransformers([
        BackedEnum::class => [OptionsTransformer::class, EnumTransformer::class],
    ]);

    $model = DummyModelWithEloquentExcludedCasts::create([
        'data' => [
            'string' => 'Test',
            'enum' => DummyBackedEnumWithOptions::CAPTAIN,
            'normal_enum' => DummyBackedEnum::FOO,
        ],
        'eloquent_excluded_enum' => DummyBackedEnumWithOptions::CAPTAIN,
    ]);

    // Ensures the value is saved properly to the database
    assertDatabaseHas($model::class, [
        'data' => json_encode(['string' => 'Test', 'enum' => 'captain', 'normal_enum' => 'foo']),
        'eloquent_excluded_enum' => 'captain',
    ]);

    // Ensures the value is serialized properly otherwise
    expect($model->jsonSerialize())->toMatchArray([
        'data' => [
            'string' => 'Test',
            'enum' => [
                ['value' => 'captain', 'label' => 'Captain'],
                ['value' => 'first_officer', 'label' => 'First Officer'],
            ],
            'normal_enum' => 'foo',
        ],
        'eloquent_excluded_enum' => 'captain',
    ]);
});

test('transforming data takes `ExcludeFromEloquentCasts` into account', function () {
    resolve(DataConfig::class)->setTransformers([
        BackedEnum::class => [OptionsTransformer::class, EnumTransformer::class],
    ]);

    $data = SimpleDataWithEloquentExcludedTransformerData::from([
        'string' => 'Test',
        'enum' => DummyBackedEnumWithOptions::CAPTAIN,
        'normal_enum' => DummyBackedEnum::FOO,
    ]);

    expect($data->transform(castingForEloquent: true))->toBe([
        'string' => 'Test',
        'enum' => 'captain',
        'normal_enum' => 'foo',
    ]);

    expect($data->transform(castingForEloquent: false))->toBe([
        'string' => 'Test',
        'enum' => [
            ['value' => 'captain', 'label' => 'Captain'],
            ['value' => 'first_officer', 'label' => 'First Officer'],
        ],
        'normal_enum' => 'foo',
    ]);
});
