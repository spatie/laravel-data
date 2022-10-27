<?php

use Carbon\CarbonImmutable;
use DateTime;
use DateTimeImmutable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataPipes\CastPropertiesDataPipe;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Tests\Fakes\BuiltInTypeWithCastData;
use Spatie\LaravelData\Tests\Fakes\ComplicatedData;
use Spatie\LaravelData\Tests\Fakes\DateCastData;
use Spatie\LaravelData\Tests\Fakes\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\DummyModel;
use Spatie\LaravelData\Tests\Fakes\EnumData;
use Spatie\LaravelData\Tests\Fakes\ModelData;
use Spatie\LaravelData\Tests\Fakes\NestedLazyData;
use Spatie\LaravelData\Tests\Fakes\NestedModelCollectionData;
use Spatie\LaravelData\Tests\Fakes\NestedModelData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

beforeEach(function () {
    $this->pipe = resolve(CastPropertiesDataPipe::class);
});

it('maps default types', function () {
    $data = ComplicatedData::from([
        'withoutType' => 42,
        'int' => 42,
        'bool' => true,
        'float' => 3.14,
        'string' => 'Hello world',
        'array' => [1, 1, 2, 3, 5, 8],
        'nullable' => null,
        'mixed' => 42,
        'explicitCast' => '16-06-1994',
        'defaultCast' => '1994-05-16T12:00:00+01:00',
        'nestedData' => [
            'string' => 'hello',
        ],
        'nestedCollection' => [
            ['string' => 'never'],
            ['string' => 'gonna'],
            ['string' => 'give'],
            ['string' => 'you'],
            ['string' => 'up'],
        ],
    ]);

    expect($data)->toBeInstanceOf(ComplicatedData::class)
        ->withoutType->toEqual(42)
        ->int->toEqual(42)
        ->bool->toBeTrue()
        ->float->toEqual(3.14)
        ->string->toEqual('Hello world')
        ->array->toEqual([1, 1, 2, 3, 5, 8])
        ->nullable->toBeNull([1, 1, 2, 3, 5, 8])
        ->undefinable->toBeInstanceOf(Optional::class)
        ->mixed->toEqual(42)
        ->defaultCast->toEqual(DateTime::createFromFormat(DATE_ATOM, '1994-05-16T12:00:00+01:00'))
        ->explicitCast->toEqual(CarbonImmutable::createFromFormat('d-m-Y', '16-06-1994'))
        ->nestedData->toEqual(SimpleData::from('hello'))
        ->nestedCollection->toEqual(SimpleData::collection([
            SimpleData::from('never'),
            SimpleData::from('gonna'),
            SimpleData::from('give'),
            SimpleData::from('you'),
            SimpleData::from('up'),
        ]));
});

it("won't cast a property that is already in the correct type", function () {
    $data = ComplicatedData::from([
        'withoutType' => 42,
        'int' => 42,
        'bool' => true,
        'float' => 3.14,
        'string' => 'Hello world',
        'array' => [1, 1, 2, 3, 5, 8],
        'nullable' => null,
        'mixed' => 42,
        'explicitCast' => DateTime::createFromFormat('d-m-Y', '16-06-1994'),
        'defaultCast' => DateTime::createFromFormat(DATE_ATOM, '1994-05-16T12:00:00+02:00'),
        'nestedData' => SimpleData::from('hello'),
        'nestedCollection' => SimpleData::collection([
            'never', 'gonna', 'give', 'you', 'up',
        ]),
    ]);

    expect($data)->toBeInstanceOf(ComplicatedData::class)
        ->withoutType->toEqual(42)
        ->int->toEqual(42)
        ->bool->toBeTrue()
        ->float->toEqual(3.14)
        ->string->toEqual('Hello world')
        ->array->toEqual([1, 1, 2, 3, 5, 8])
        ->nullable->toBeNull([1, 1, 2, 3, 5, 8])
        ->mixed->toBe(42)
        ->defaultCast->toEqual(DateTime::createFromFormat(DATE_ATOM, '1994-05-16T12:00:00+02:00'))
        ->explicitCast->toEqual(DateTime::createFromFormat('d-m-Y', '16-06-1994'))
        ->nestedData->toEqual(SimpleData::from('hello'))
        ->nestedCollection->toEqual(SimpleData::collection([
            SimpleData::from('never'),
            SimpleData::from('gonna'),
            SimpleData::from('give'),
            SimpleData::from('you'),
            SimpleData::from('up'),
        ]));
});

it('will allow a nested data object to handle their own types', function () {
    $model = new DummyModel(['id' => 10]);

    $withoutModelData = NestedModelData::from([
        'model' => ['id' => 10],
    ]);

    expect($withoutModelData)
        ->toBeInstanceOf(NestedModelData::class)
        ->model->id->toEqual(10);

    /** @var \Spatie\LaravelData\Tests\Fakes\NestedModelData $data */
    $withModelData = NestedModelData::from([
        'model' => $model,
    ]);

    expect($withModelData)
        ->toBeInstanceOf(NestedModelData::class)
        ->model->id->toEqual(10);
});

it('will allow a nested collection object to handle its own types', function () {
    $data = NestedModelCollectionData::from([
        'models' => [['id' => 10], ['id' => 20],],
    ]);

    expect($data)->toBeInstanceOf(NestedModelCollectionData::class)
        ->models->toEqual(ModelData::collection([['id' => 10], ['id' => 20]]));

    $data = NestedModelCollectionData::from([
        'models' => [new DummyModel(['id' => 10]), new DummyModel(['id' => 20]),],
    ]);

    expect($data)->toBeInstanceOf(NestedModelCollectionData::class)
        ->models->toEqual(ModelData::collection([['id' => 10], ['id' => 20]]));

    $data = NestedModelCollectionData::from([
        'models' => ModelData::collection([['id' => 10], ['id' => 20]]),
    ]);

    expect($data)->toBeInstanceOf(NestedModelCollectionData::class)
        ->models->toEqual(ModelData::collection([['id' => 10], ['id' => 20]]));
});

it('works nicely with lazy data', function () {
    $data = NestedLazyData::from([
        'simple' => Lazy::create(fn () => SimpleData::from('Hello')),
    ]);

    expect($data->simple)
        ->toBeInstanceOf(Lazy::class)
        ->toEqual(Lazy::create(fn () => SimpleData::from('Hello')));
});

it('allows casting of built-in types', function () {
    $data = BuiltInTypeWithCastData::from([
        'money' => 3.14,
    ]);

    expect($data->money)->toBeInt()->toEqual(314);
});

it('allows casting', function () {
    $data = DateCastData::from([
        'date' => '2022-01-18',
    ]);

    expect($data->date)
        ->toBeInstanceOf(DateTimeImmutable::class)
        ->toEqual(DateTimeImmutable::createFromFormat('Y-m-d', '2022-01-18'));
});

it('allows casting of enums', function () {
    $this->onlyPHP81();

    $data = EnumData::from([
        'enum' => 'foo',
    ]);

    expect($data->enum)
        ->toBeInstanceOf(DummyBackedEnum::class)
        ->toEqual(DummyBackedEnum::FOO);
});

it('can manually set values in the constructor', function () {
    $dataClass = new class('', '') extends Data
    {
        public string $member;

        public string $other_member;

        public string $member_with_default = 'default';

        public string $member_to_set;

        public function __construct(
            public string $promoted,
            string $non_promoted,
            string $non_promoted_with_default = 'default',
            public string $promoted_with_with_default = 'default',
        ) {
            $this->member = "changed_in_constructor: {$non_promoted}";
            $this->other_member = "changed_in_constructor: {$non_promoted_with_default}";
        }
    };

    $data = $dataClass::from([
        'promoted' => 'A',
        'non_promoted' => 'B',
        'non_promoted_with_default' => 'C',
        'promoted_with_with_default' => 'D',
        'member_to_set' => 'E',
        'member_with_default' => 'F',
    ]);

    expect($data->toArray())->toMatchArray([
        'member' => 'changed_in_constructor: B',
        'other_member' => 'changed_in_constructor: C',
        'member_with_default' => 'F',
        'promoted' => 'A',
        'promoted_with_with_default' => 'D',
        'member_to_set' => 'E',
    ]);

    $data = $dataClass::from([
        'promoted' => 'A',
        'non_promoted' => 'B',
        'member_to_set' => 'E',
    ]);

    expect($data->toArray())->toMatchArray([
        'member' => 'changed_in_constructor: B',
        'other_member' => 'changed_in_constructor: default',
        'member_with_default' => 'default',
        'promoted' => 'A',
        'promoted_with_with_default' => 'default',
        'member_to_set' => 'E',
    ]);
});
