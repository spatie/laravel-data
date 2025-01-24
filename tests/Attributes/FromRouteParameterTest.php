<?php

namespace Spatie\LaravelData\Tests\Attributes;

use Illuminate\Http\Request;

use function Pest\Laravel\mock;

use Spatie\LaravelData\Attributes\FromRouteParameter;
use Spatie\LaravelData\Data;

test('it can get a route parameter', function () {
    $dataClass = new class () extends Data {
        #[FromRouteParameter('parameter')]
        public string $property;
    };

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('parameter')->once()->andReturns('test');
    $requestMock->expects('toArray')->andReturns([]);

    expect($dataClass::from($requestMock)->property)->toBe('test');
});

it('wont replace a route parameter if the payload is not a request', function () {
    $dataClass = new class () extends Data {
        #[FromRouteParameter('parameter')]
        public string $property;
    };

    expect(isset($dataClass::from(['parameter' => 'test'])->property))->toBeFalse();
});
