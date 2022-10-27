<?php

use function Pest\Laravel\assertDatabaseHas;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Tests\Fakes\DummyModelWithCasts;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

beforeEach(function () {
    DummyModelWithCasts::migrate();
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

    /** @var \Spatie\LaravelData\Tests\Fakes\DummyModelWithCasts $model */
    $model = DummyModelWithCasts::first();

    expect($model->data_collection)->toEqual(SimpleData::collection([
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

    /** @var \Spatie\LaravelData\Tests\Fakes\DummyModelWithCasts $model */
    $model = DummyModelWithCasts::first();

    expect($model->data_collection)->toBeNull();
});
