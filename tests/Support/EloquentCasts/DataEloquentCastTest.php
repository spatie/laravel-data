<?php

use Illuminate\Support\Facades\DB;

use function Pest\Laravel\assertDatabaseHas;

use Spatie\LaravelData\Tests\Fakes\HiddenData;

use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCasts;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCastsWithHidden;
use Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithDefaultCasts;

use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithDefaultValue;

beforeEach(function () {
    DummyModelWithCasts::migrate();
    DummyModelWithCastsWithHidden::migrate();
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

it('can save a data object with hidden', function () {
    DummyModelWithCastsWithHidden::create([
        'data' => new HiddenData(string: 'Test', hidden: 'Hidden'),
    ]);

    assertDatabaseHas(DummyModelWithCastsWithHidden::class, [
        'data' => json_encode(['string' => 'Test', 'hidden' => 'Hidden']),
    ]);
});

it('can save a data object as an array with hidden', function () {
    DummyModelWithCastsWithHidden::create([
        'data' => ['string' => 'Test', 'hidden' => 'Hidden'],
    ]);

    assertDatabaseHas(DummyModelWithCastsWithHidden::class, [
        'data' => json_encode(['string' => 'Test', 'hidden' => 'Hidden']),
    ]);
});

it('can load a data object with hidden', function () {
    DB::table('dummy_model_with_casts_with_hiddens')->insert([
        'data' => json_encode(['string' => 'Test', 'hidden' => 'Hidden']),
    ]);

    /** @var \Spatie\LaravelData\Tests\Fakes\Models\DummyModelWithCasts $model */
    $model = DummyModelWithCastsWithHidden::first();

    expect($model->data)->toEqual(new HiddenData(string: 'Test', hidden: 'Hidden'));
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
