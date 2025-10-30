<?php

namespace Spatie\LaravelData\Tests\Attributes;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;

use function Pest\Laravel\mock;

use Spatie\LaravelData\Attributes\FromRouteDefault;
use Spatie\LaravelData\Data;

test('it can get a value from route defaults', function () {
    $dataClass = new class () extends Data {
        #[FromRouteDefault('value')]
        public string $value;
    };

    $route = mock(Route::class);
    $route->defaults = ['value' => 'test'];

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->andReturns($route);
    $requestMock->expects('toArray')->andReturns([]);

    expect($dataClass::from($requestMock)->value)->toBe('test');
});

test('it wont fill property if default does not exist', function () {
    $dataClass = new class () extends Data {
        #[FromRouteDefault('missing')]
        public ?string $missing;
    };

    $route = mock(Route::class);
    $route->defaults = [];

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->andReturns($route);
    $requestMock->expects('toArray')->andReturns([]);

    expect(isset($dataClass::from($requestMock)->missing))->toBeFalse();
});

it('wont fill property from attribute if the payload is not a request', function () {
    $dataClass = new class () extends Data {
        #[FromRouteDefault('other')]
        public ?string $value;
    };

    $data = $dataClass::from(['value' => 'test']);

    expect($data->value)->toBe('test')
        ->and(isset($data->other))->toBeFalse();
});

test('it can work with enum values in defaults', function () {
    enum Status: string
    {
        case ACTIVE = 'active';
        case INACTIVE = 'inactive';
    }

    $dataClass = new class () extends Data {
        #[FromRouteDefault('status')]
        public Status $status;
    };

    $route = mock(Route::class);
    $route->defaults = ['status' => Status::ACTIVE];

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->andReturns($route);
    $requestMock->expects('toArray')->andReturns([]);

    expect($dataClass::from($requestMock)->status)->toBe(Status::ACTIVE);
});

it('can use route defaults when replaceWhenPresentInPayload is enabled', function () {
    $dataClass = new class () extends Data {
        #[FromRouteDefault('key')]
        public string $key;
    };

    $route = mock(Route::class);
    $route->defaults = ['key' => 'default_value'];

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->andReturns($route);
    $requestMock->expects('toArray')->andReturns(['key' => 'payload_value']);

    expect($dataClass::from($requestMock)->key)->toBe('default_value');
});

it('can use payload value when replaceWhenPresentInPayload is disabled', function () {
    $dataClass = new class () extends Data {
        #[FromRouteDefault('key', replaceWhenPresentInPayload: false)]
        public string $key;
    };

    $route = mock(Route::class);
    $route->defaults = ['key' => 'default_value'];

    $requestMock = mock(Request::class);
    $requestMock->allows('route')->andReturns($route);
    $requestMock->expects('toArray')->andReturns(['key' => 'payload_value']);

    expect($dataClass::from($requestMock)->key)->toBe('payload_value');
});
