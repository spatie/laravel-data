<?php

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataParameter;
use Spatie\LaravelData\Support\DataType;

it('can create a data parameter', function () {
    $class = new class ('', '', '') extends Data {
        public function __construct(
            string $nonPromoted,
            public $withoutType,
            public string $property,
            public string $propertyWithDefault = 'hello',
        ) {
        }
    };

    $reflection = new ReflectionParameter([$class::class, '__construct'], 'nonPromoted');
    $parameter = DataParameter::create($reflection);

    expect($parameter)
        ->name->toEqual('nonPromoted')
        ->isPromoted->toBeFalse()
        ->hasDefaultValue->toBeFalse()
        ->defaultValue->toBeNull()
        ->type->toEqual(DataType::create($reflection));

    $reflection = new ReflectionParameter([$class::class, '__construct'], 'withoutType');
    $parameter = DataParameter::create($reflection);

    expect($parameter)
        ->name->toEqual('withoutType')
        ->isPromoted->toBeTrue()
        ->hasDefaultValue->toBeFalse()
        ->defaultValue->toBeNull()
        ->type->toEqual(DataType::create($reflection));

    $reflection = new ReflectionParameter([$class::class, '__construct'], 'property');
    $parameter = DataParameter::create($reflection);

    expect($parameter)
        ->name->toEqual('property')
        ->isPromoted->toBeTrue()
        ->hasDefaultValue->toBeFalse()
        ->defaultValue->toBeNull()
        ->type->toEqual(DataType::create($reflection));

    $reflection = new ReflectionParameter([$class::class, '__construct'], 'propertyWithDefault');
    $parameter = DataParameter::create($reflection);

    expect($parameter)
        ->name->toEqual('propertyWithDefault')
        ->isPromoted->toBeTrue()
        ->hasDefaultValue->toBeTrue()
        ->defaultValue->toEqual('hello')
        ->type->toEqual(DataType::create($reflection));
});
