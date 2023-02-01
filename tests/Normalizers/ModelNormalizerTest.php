<?php

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Tests\Fakes\FakeModelData;
use Spatie\LaravelData\Tests\Fakes\Models\FakeModel;
use Spatie\LaravelData\Tests\Fakes\Models\FakeNestedModel;

it('can get a data object from model', function () {
    $model = FakeModel::factory()->create();
    $data = FakeModelData::from($model);

    expect($model)
        ->string->toEqual($data->string)
        ->nullable->toEqual($data->nullable)
        ->date->toEqual($data->date);
});

it('can get a data object with nesting from model and relations', function () {
    $model = FakeModel::factory()->create();

    $nestedModelA = FakeNestedModel::factory()->for($model)->create();
    $nestedModelB = FakeNestedModel::factory()->for($model)->create();

    $data = FakeModelData::from($model->load('fakeNestedModels'));

    expect($model)
        ->string->toEqual($data->string)
        ->nullable->toEqual($data->nullable)
        ->date->toEqual($data->date);

    expect($data->fake_nested_models)->toHaveCount(2);

    expect($nestedModelA)
        ->string->toEqual($data->fake_nested_models[0]->string)
        ->nullable->toEqual($data->fake_nested_models[0]->nullable)
        ->date->toEqual($data->fake_nested_models[0]->date)
        ->and($nestedModelB)
        ->string->toEqual($data->fake_nested_models[1]->string)
        ->nullable->toEqual($data->fake_nested_models[1]->nullable)
        ->date->toEqual($data->fake_nested_models[1]->date);
});

it('can get a data object from model with dates', function () {
    $fakeModelClass = new class () extends Model {
        protected $casts = [
            'date' => 'date',
            'datetime' => 'datetime',
            'immutable_date' => 'immutable_date',
            'immutable_datetime' => 'immutable_datetime',
        ];
    };

    $model = $fakeModelClass::make([
        'date' => Carbon::create(2020, 05, 16, 12, 00, 00),
        'datetime' => Carbon::create(2020, 05, 16, 12, 00, 00),
        'immutable_date' => Carbon::create(2020, 05, 16, 12, 00, 00),
        'immutable_datetime' => Carbon::create(2020, 05, 16, 12, 00, 00),
        'created_at' => Carbon::create(2020, 05, 16, 12, 00, 00),
        'updated_at' => Carbon::create(2020, 05, 16, 12, 00, 00),
    ]);

    class TestDataFromModelWithDates extends Data
    {
        public function __construct(
            public Carbon $date,
            public Carbon $datetime,
            public CarbonImmutable $immutable_date,
            public CarbonImmutable $immutable_datetime,
            public Carbon $created_at,
            public Carbon $updated_at,
        ) {
        }
    }

    $data = \TestDataFromModelWithDates::from($model);

    expect([
        $data->date->eq(Carbon::create(2020, 05, 16, 00, 00, 00)),
        $data->datetime->eq(Carbon::create(2020, 05, 16, 12, 00, 00)),
        $data->immutable_date->eq(Carbon::create(2020, 05, 16, 00, 00, 00)),
        $data->immutable_datetime->eq(Carbon::create(2020, 05, 16, 12, 00, 00)),
        $data->created_at->eq(Carbon::create(2020, 05, 16, 12, 00, 00)),
        $data->updated_at->eq(Carbon::create(2020, 05, 16, 12, 00, 00)),
    ])->each->toBeTrue();
});
