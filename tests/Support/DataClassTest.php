<?php

use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataMethod;
use Spatie\LaravelData\Tests\DataWithDefaults;
use Spatie\LaravelData\Tests\Fakes\DataWithMapper;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('keeps track of a global map from attribute', function () {
    $dataClass = DataClass::create(new ReflectionClass(DataWithMapper::class));

    expect($dataClass->properties->get('casedProperty')->inputMappedName)
        ->toEqual('cased_property')
        ->and($dataClass->properties->get('casedProperty')->outputMappedName)
        ->toEqual('cased_property');
});

it('will provide information about special methods', function () {
    $class = DataClass::create(new ReflectionClass(SimpleData::class));

    expect($class->methods)->toHaveKey('fromString')
        ->and($class->methods->get('fromString'))
        ->toBeInstanceOf(DataMethod::class);
});

it('will provide information about the constructor', function () {
    $class = DataClass::create(new ReflectionClass(SimpleData::class));

    expect($class->constructorMethod)
        ->not->toBeNull()
        ->toBeInstanceOf(DataMethod::class);
});

it('will populate defaults to properties when they exist ', function () {
    /** @var \Spatie\LaravelData\Support\DataProperty[] $properties */
    $properties = DataClass::create(new ReflectionClass(DataWithDefaults::class))->properties->values();

    expect($properties[0])
        ->name->toEqual('property')
        ->hasDefaultValue->toBeFalse();

    expect($properties[1])
        ->name->toEqual('default_property')
        ->hasDefaultValue->toBeTrue()
        ->defaultValue->toEqual('Hello');

    expect($properties[2])
        ->name->toEqual('promoted_property')
        ->hasDefaultValue->toBeFalse();

    expect($properties[3])
        ->name->toEqual('default_promoted_property')
        ->hasDefaultValue->toBeTrue()
        ->defaultValue->toEqual('Hello Again');
});
