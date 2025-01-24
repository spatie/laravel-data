<?php

namespace Spatie\LaravelData\Tests\Attributes;

use Illuminate\Http\Request;

use function Pest\Laravel\mock;

use Spatie\LaravelData\Attributes\FromRouteParameterProperty;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Exceptions\CannotFillFromRouteParameterPropertyUsingScalarValue;

it('can get a route parameter property value based upon the property name', function () {
    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('parameter')->once()->andReturns([
        'property' => 'Hello World',
    ]);
    $requestMock->expects('toArray')->andReturns([]);

    $dataClass = new class () extends Data {
        #[FromRouteParameterProperty('parameter')]
        public string $property;
    };

    expect($dataClass::from($requestMock)->property)->toBe('Hello World');
});

it('can get a user property value based upon a key defined in the attribute', function () {
    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('parameter')->once()->andReturns([
        'test' => 'Hello World',
    ]);
    $requestMock->expects('toArray')->andReturns([]);

    $dataClass = new class () extends Data {
        #[FromRouteParameterProperty('parameter', property: 'test')]
        public string $property;
    };

    expect($dataClass::from($requestMock)->property)->toBe('Hello World');
});

it('throws an exception when trying to fill a route parameter property using a scalar value', function () {
    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('parameter')->once()->andReturns('not-valid');
    $requestMock->expects('toArray')->andReturns([]);

    $dataClass = new class () extends Data {
        #[FromRouteParameterProperty('parameter')]
        public string $property;
    };

    $dataClass::from($requestMock);
})->throws(CannotFillFromRouteParameterPropertyUsingScalarValue::class);
