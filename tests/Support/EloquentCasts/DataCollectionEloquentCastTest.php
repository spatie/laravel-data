<?php

use Illuminate\Support\Facades\DB;

use function Pest\Laravel\assertDatabaseHas;

use Spatie\LaravelData\Tests\Fakes\HiddenData;

use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCasts;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCastsWithHidden;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCustomCollectionCasts;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithDefaultCasts;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataCollection;

beforeEach(function () {
    DummyModelWithCasts::migrate();
    DummyModelWithCastsWithHidden::migrate();
});

it('can save a data collection', function () {
    DummyModelWithCasts::create([
        'data_collection' => SimpleData::collection([
            new SimpleData('Hello'),
            new SimpleData('World'),
        ]),
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

it('can load a data object', function () {
    DB::table('dummy_model_with_casts')->insert([
        'data_collection' => json_encode([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]),
    ]);

    /** @var \Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCasts $model */
    $model = DummyModelWithCasts::first();

    expect($model->data_collection)->toEqual(SimpleData::collection([
        new SimpleData('Hello'),
        new SimpleData('World'),
    ]));
});

it('can save a data collection with hidden', function () {
    DummyModelWithCastsWithHidden::create([
        'data_collection' => HiddenData::collection([
            new HiddenData(string: 'Hello', hidden: 'World'),
            new HiddenData(string: 'World', hidden: 'Hello'),
        ]),
    ]);

    assertDatabaseHas(DummyModelWithCastsWithHidden::class, [
        'data_collection' => json_encode([
            ['string' => 'Hello', 'hidden' => 'World'],
            ['string' => 'World', 'hidden' => 'Hello'],
        ]),
    ]);
});

it('can save a data object as an array with hidden', function () {
    DummyModelWithCastsWithHidden::create([
        'data_collection' => [
            ['string' => 'Hello', 'hidden' => 'World'],
            ['string' => 'World', 'hidden' => 'Hello'],
        ],
    ]);

    assertDatabaseHas(DummyModelWithCastsWithHidden::class, [
        'data_collection' => json_encode([
            ['string' => 'Hello', 'hidden' => 'World'],
            ['string' => 'World', 'hidden' => 'Hello'],
        ]),
    ]);
});

it('can load a data object with hidden', function () {
    DB::table('dummy_model_with_casts_with_hiddens')->insert([
        'data_collection' => json_encode([
            ['string' => 'Hello', 'hidden' => 'World'],
            ['string' => 'World', 'hidden' => 'Hello'],
        ]),
    ]);

    /** @var \Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCastsWithHidden $model */
    $model = DummyModelWithCastsWithHidden::first();

    expect($model->data_collection)->toEqual(HiddenData::collection([
        new HiddenData(string: 'Hello', hidden: 'World'),
        new HiddenData(string: 'World', hidden: 'Hello'),
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
