<?php

namespace Spatie\LaravelData\Tests\Attributes;

use Spatie\LaravelData\Attributes\FromContainerProperty;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Exceptions\CannotFillFromRouteParameterPropertyUsingScalarValue;

it('can get a container property value based upon the property name', function () {
    app()->bind('test', fn () => [
        'property' => 'defined',
    ]);

    $dataClass = new class () extends Data {
        #[FromContainerProperty('test')]
        public string $property;
    };

    expect($dataClass::from()->property)->toBe('defined');
});

it('can get a user property value based upon a key defined in the attribute', function () {
    app()->bind('test', fn () => [
        'property' => 'defined',
    ]);

    $dataClass = new class () extends Data {
        #[FromContainerProperty('test', property: 'property')]
        public string $value;
    };

    expect($dataClass::from()->value)->toBe('defined');
});


it('throws an exception when trying to fill a dependency property using a scalar value', function () {
    app()->bind('test', fn () => 'not-valid');

    $dataClass = new class () extends Data {
        #[FromContainerProperty('test')]
        public string $property;
    };

    $dataClass::from();
})->throws(CannotFillFromRouteParameterPropertyUsingScalarValue::class);
