<?php

use Illuminate\Http\Request;

use function Pest\Laravel\mock;

use Spatie\LaravelData\Attributes\FromRouteModel;

use Spatie\LaravelData\Data;

it('can fill data properties from a route model', function () {
    $dataClass = new class () extends Data {
        #[FromRouteModel('something')]
        public int $id;
    };

    $somethingMock = new class () {
        public int $id = 123;
    };

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('something')->once()->andReturns($somethingMock);
    $requestMock->expects('toArray')->andReturns([]);

    $data = $dataClass::from($requestMock);

    expect($data->id)->toEqual(123);
});

it('can fill data properties from a route model using custom property mapping ', function () {
    $dataClass = new class () extends Data {
        #[FromRouteModel('something', 'name')]
        public string $title;
    };

    $somethingMock = new class () {
        public string $name = 'Something';
    };

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('something')->once()->andReturns($somethingMock);
    $requestMock->expects('toArray')->andReturns([]);

    $data = $dataClass::from($requestMock);

    expect($data->title)->toEqual('Something');
});

it('replaces properties when route model properties exist', function () {
    $dataClass = new class () extends Data {
        #[FromRouteModel('something')]
        public string $name;
    };

    $somethingMock = new class () {
        public string $name = 'Best';
    };

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('something')->once()->andReturns($somethingMock);
    $requestMock->expects('toArray')->andReturns(['title' => 'Better']);

    $data = $dataClass::from($requestMock);

    expect($data->name)->toEqual('Best');
});

it('skips replacing properties when route model properties exist and replacing is disabled', function () {
    $dataClass = new class () extends Data {
        #[FromRouteModel('something', replace: false)]
        public string $name;
        #[FromRouteModel('something', 'long', false)]
        public string $description;
    };

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('something')->never();
    $requestMock->expects('toArray')->andReturns(['name' => 'Better', 'description' => 'Description']);

    $data = $dataClass::from($requestMock);

    expect($data->name)->toEqual('Better');
    expect($data->description)->toEqual('Description');
});

it('skips properties it cannot find a route model for', function () {
    $dataClass = new class () extends Data {
        #[FromRouteModel('something')]
        public string $name;
    };

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('something')->once()->andReturnNull();
    $requestMock->expects('toArray')->andReturns(['name' => 'Better']);

    $data = $dataClass::from($requestMock);

    expect($data->name)->toEqual('Better');
});
