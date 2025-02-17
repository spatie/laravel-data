<?php

use Illuminate\Database\LazyLoadingViolationException;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\LoadRelation;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;
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

it('does not loop infinitely on relations', function () {
    $parentModel = FakeModel::factory()->makeOne();
    $childModel = FakeNestedModel::factory()->makeOne();

    $childModel->setRelation('parent', $parentModel);
    $parentModel->setRelation('pivot', $childModel);

    $data = FakeModelData::from($parentModel);

    expect($parentModel)
        ->string->toEqual($data->string)
        ->nullable->toEqual($data->nullable)
        ->date->toEqual($data->date);
});

it('can get a data object with nesting from model and relations when loaded', function () {
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

it('can get a data object from model with accessors', function () {
    $model = FakeModel::factory()->create();
    $data = FakeModelData::from($model);

    expect($model)
        ->accessor->toEqual($data->accessor)
        ->old_accessor->toEqual($data->old_accessor);
});

it('will only call model accessors when required', function () {
    $dataClass = new class () extends Data {
        public string $accessor;

        public string $old_accessor;
    };

    $dataClass::from(FakeModel::factory()->create());

    $dataClass = new class () extends Data {
        public string $performance_heavy;
    };

    expect(fn () => $dataClass::from(FakeModel::factory()->create()))->toThrow(
        Exception::class,
        'This attribute should not be called'
    );

    $dataClass = new class () extends Data {
        public string $performance_heavy_accessor;
    };

    expect(fn () => $dataClass::from(FakeModel::factory()->create()))->toThrow(
        Exception::class,
        'This accessor should not be called'
    );
});

it('will return null for non-existing properties', function () {
    $dataClass = new class () extends Data {
        public ?string $non_existing_property;
    };

    $data = $dataClass::from(FakeModel::factory()->create());

    expect($data->non_existing_property)->toBeNull();
});

it('can load relations on a model when required and the LoadRelation attribute is set', function () {
    $model = FakeModel::factory()->create();

    FakeNestedModel::factory()->for($model)->create();
    FakeNestedModel::factory()->for($model)->create();

    $dataClass = new class () extends Data {
        #[LoadRelation, DataCollectionOf(FakeNestedModelData::class)]
        public array $fake_nested_models;

        #[LoadRelation, DataCollectionOf(FakeNestedModelData::class)]
        public array $fake_nested_models_snake_cased;
    };

    $model->load('fake_nested_models_snake_cased');
    DB::enableQueryLog();

    $data = $dataClass::from($model);

    $queryLog = DB::getQueryLog();

    expect($data->fake_nested_models)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(FakeNestedModelData::class);

    expect($data->fake_nested_models_snake_cased)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(FakeNestedModelData::class);
    expect($queryLog)->toHaveCount(1);
});

it('will not automatically load relation when the LoadRelation attribute is not set', function () {
    $model = FakeModel::factory()->create();

    FakeNestedModel::factory()->for($model)->create();
    FakeNestedModel::factory()->for($model)->create();

    $dataClass = new class () extends Data {
        #[DataCollectionOf(FakeNestedModelData::class)]
        public array|Optional $fake_nested_models;
    };

    DB::enableQueryLog();

    $data = $dataClass::from($model);

    $queryLog = DB::getQueryLog();

    expect($data->fake_nested_models)->toBeInstanceOf(Optional::class);
    expect($queryLog)->toHaveCount(0);

    $dataClass = new class () extends Data {
        public array|null $fake_nested_models = null;
    };

    DB::enableQueryLog();

    $data = $dataClass::from($model);

    $queryLog = DB::getQueryLog();

    expect($data->fake_nested_models)->toBeNull();
    expect($queryLog)->toHaveCount(0);
});

it('can use mappers to map the names', function () {
    $model = FakeModel::factory()->create();

    FakeNestedModel::factory()->for($model)->create();
    FakeNestedModel::factory()->for($model)->create();

    $dataClass = new class () extends Data {
        #[DataCollectionOf(FakeNestedModelData::class), MapInputName(SnakeCaseMapper::class)]
        public array|Optional $fakeNestedModels;

        #[MapInputName(SnakeCaseMapper::class)]
        public string $oldAccessor;
    };

    $data = $dataClass::from($model->load('fakeNestedModels'));

    expect($data->fakeNestedModels)
        ->toHaveCount(2)
        ->each()
        ->toBeInstanceOf(FakeNestedModelData::class);

    expect($data)->oldAccessor->toEqual($model->old_accessor);
});

it('can create a data property for a model attribute which fetches a relation that is loaded and it will not trigger a lazy loading exception', function () {
    $dataClass = new class ('') extends Data {
        public function __construct(public string $accessor_using_relation)
        {
        }
    };

    $model = FakeModel::factory()->create();
    FakeNestedModel::factory()->for($model)->create();

    $freshModel = FakeModel::query()->first();

    $freshModel->preventsLazyLoading = true;

    expect(function () use ($dataClass, $freshModel) {
        $freshModel->append('accessorUsingRelation');

        $dataClass::from($freshModel);
    })->toThrow(LazyLoadingViolationException::class);

    $freshModel = $freshModel
        ->load('fakeNestedModels')
        ->append('accessorUsingRelation');

    $data = $dataClass::from($freshModel);

    expect($freshModel)
        ->accessor_using_relation
        ->toEqual($data->accessor_using_relation);
});
