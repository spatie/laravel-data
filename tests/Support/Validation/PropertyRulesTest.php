<?php

use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Prohibited;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\RequiredUnless;
use Spatie\LaravelData\Attributes\Validation\RequiredWith;
use Spatie\LaravelData\Support\Validation\PropertyRules;
use Spatie\LaravelData\Support\Validation\RequiringRule;

it('can add rules', function () {
    $collection = PropertyRules::create()
        ->add(new Required())
        ->add(new Prohibited(), new Min(0));

    expect($collection->all())->toMatchArray([
        new Required(), new Prohibited(), new Min(0),
    ]);
});

it('will remove the rule if a new version is added', function () {
    $collection = PropertyRules::create()
        ->add(new Min(10))
        ->add(new Min(314));

    expect($collection->all())->toEqual([new Min(314)]);
});

it('can remove rules by type', function () {
    $collection = PropertyRules::create()
        ->add(new Min(10))
        ->removeType(new Min(314));

    expect($collection->all())->toEqual([]);
});

it('can remove rules by class', function () {
    $collection = PropertyRules::create()
        ->add(new Min(10))
        ->removeType(Min::class);

    expect($collection->all())->toEqual([]);
});

it('can add multiple conditional requiring rules', function () {
    $collection = PropertyRules::create()
        ->add(new RequiredUnless('requires_a', false))
        ->add(new RequiredWith('b'))
        ->add(new RequiredWith('c'));

    expect($collection->all())->toEqual([
        new RequiredUnless('requires_a', false),
        new RequiredWith('b'),
        new RequiredWith('c'),
    ]);
});

it('will only keep one plain required rule', function () {
    $collection = PropertyRules::create()
        ->add(new Required())
        ->add(new Required());

    expect($collection->all())->toEqual([
        new Required(),
    ]);
});

it('can remove all requiring rules by interface', function () {
    $collection = PropertyRules::create()
        ->add(new RequiredUnless('requires_a', false))
        ->add(new RequiredWith('b'))
        ->removeType(RequiringRule::class);

    expect($collection->all())->toEqual([]);
});
