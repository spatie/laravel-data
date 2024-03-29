<?php

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Lazy\DefaultLazy;
use Spatie\LaravelData\Tests\Fakes\FakeModelData;
use Spatie\LaravelData\Tests\Fakes\FakeNestedModelData;
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


it('converts non loaded relations to either a lazy or load them if non nullable', function () {
    $model = FakeModel::factory()->create();

    FakeNestedModel::factory()->for($model)->create();
    FakeNestedModel::factory()->for($model)->create();

    $data = FakeModelData::from($model);

    expect($data->toArray())->not()->toHaveKey('fake_nested_models')
        ->and($data->fake_nested_models)->toBeInstanceOf(Optional::class);

    class FakeModelDataNonNullable extends Data
    {
        #[DataCollectionOf(FakeNestedModelData::class)]
        public DataCollection $fake_nested_models;
    }
    $data = FakeModelDataNonNullable::from($model->withoutRelations());

    expect($data->toArray())->toHaveKey('fake_nested_models')
        ->and($data->fake_nested_models)->toHaveCount(2);

    class FakeModelDataLazyNonNullable extends Data
    {
        #[DataCollectionOf(FakeNestedModelData::class)]
        public Lazy|DataCollection $fake_nested_models;
    }

    $data = FakeModelDataLazyNonNullable::from($model->withoutRelations());

    expect($data->toArray())->toHaveKey('fake_nested_models')
        ->and($data->fake_nested_models)->toBeInstanceOf(DefaultLazy::class);

    class FakeModelDataLazyNullable extends Data
    {
        #[DataCollectionOf(FakeNestedModelData::class)]
        public null|Lazy|DataCollection $fake_nested_models;
    }

    $data = FakeModelDataLazyNullable::from($model->withoutRelations());

    expect($data->toArray())->not()->toHaveKey('fake_nested_models')
        ->and($data->fake_nested_models)->toBeInstanceOf(Lazy::class)
        ->and($data->fake_nested_models->resolve())->toHaveCount(2);


    class FakeModelDataNullable extends Data
    {
        #[DataCollectionOf(FakeNestedModelData::class)]
        public null|DataCollection $fake_nested_models;
    }

    $data = FakeModelDataNullable::from($model->withoutRelations());

    expect($data->toArray())->toHaveKey('fake_nested_models')
        ->and($data->fake_nested_models)->toBeNull();
});

it('can get a data object from model with accessors', function () {
    $model = FakeModel::factory()->create();
    $data = FakeModelData::from($model);

    expect($model)
        ->accessor->toEqual($data->accessor)
        ->old_accessor->toEqual($data->old_accessor);
});
