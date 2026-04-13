<?php

use Spatie\LaravelData\Resolvers\DataClassFromValidationPayloadResolver;
use Spatie\LaravelData\Support\Validation\ValidationPath;
use Spatie\LaravelData\Tests\Fakes\AbstractPropertyMorphableData;
use Spatie\LaravelData\Tests\Fakes\PropertyMorphableDataA;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('returns the data class for non-morphable classes', function () {
    $dataClass = app(DataClassFromValidationPayloadResolver::class)->execute(
        SimpleData::class,
        ['string' => 'Hello'],
        ValidationPath::create(),
    );

    expect($dataClass->name)->toBe(SimpleData::class);
});

it('returns the morphed data class based on the payload', function () {
    $dataClass = app(DataClassFromValidationPayloadResolver::class)->execute(
        AbstractPropertyMorphableData::class,
        ['variant' => 'a', 'a' => 'test', 'enum' => 'foo'],
        ValidationPath::create(),
    );

    expect($dataClass->name)->toBe(PropertyMorphableDataA::class);
});

it('returns the abstract data class when morph cannot be resolved', function () {
    $dataClass = app(DataClassFromValidationPayloadResolver::class)->execute(
        AbstractPropertyMorphableData::class,
        ['variant' => 'unknown'],
        ValidationPath::create(),
    );

    expect($dataClass->name)->toBe(AbstractPropertyMorphableData::class);
});

it('resolves the morphed data class from a nested path', function () {
    $dataClass = app(DataClassFromValidationPayloadResolver::class)->execute(
        AbstractPropertyMorphableData::class,
        ['nested' => ['variant' => 'a', 'a' => 'test', 'enum' => 'foo']],
        ValidationPath::create('nested'),
    );

    expect($dataClass->name)->toBe(PropertyMorphableDataA::class);
});
