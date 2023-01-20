<?php

use Illuminate\Validation\Rules\Enum as BaseEnum;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Prohibited;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Support\Validation\RulesCollection;
use Spatie\LaravelData\Tests\Fakes\FakeEnum;

it('can add rules', function () {
    $collection = RulesCollection::create()
        ->add(new Required())
        ->add(new Prohibited(), new Min(0));

    expect($collection->all())->toMatchArray([
        new Required(), new Prohibited(), new Min(0),
    ]);
});

it('will remove the rule if a new version is added', function () {
    $collection = RulesCollection::create()
        ->add(new Min(10))
        ->add(new Min(314));

    expect($collection->all())->toEqual([new Min(314)]);
});

it('can remove rules by type', function () {
    $collection = RulesCollection::create()
        ->add(new Min(10))
        ->removeType(new Min(314));

    expect($collection->all())->toEqual([]);
});

it('can remove rules by class', function () {
    $collection = RulesCollection::create()
        ->add(new Min(10))
        ->removeType(Min::class);

    expect($collection->all())->toEqual([]);
});

it('can normalize rules', function () {
    $collection = RulesCollection::create()
        ->add(new Min(10))
        ->add(new Required())
        ->add(new Enum(FakeEnum::class));

    expect($collection)
        ->all()->toEqual([new Min(10), new Required(), new Enum(FakeEnum::class)])
        ->normalize(null)->toEqual([new Min(10), new Required(), new BaseEnum(FakeEnum::class)]);
});
