<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use Spatie\LaravelData\Attributes\MapInputName;
use function Pest\Laravel\mock;

use Spatie\LaravelData\Attributes\FromRouteParameter;
use Spatie\LaravelData\Attributes\FromRouteParameterProperty;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Exceptions\CannotFillFromRouteParameterPropertyUsingScalarValue;
use Spatie\LaravelData\Tests\Fakes\NestedData;

it('can fill data properties with route parameters', function () {
    $dataClass = new class () extends Data {
        #[FromRouteParameter('id')]
        public int $id;
        #[FromRouteParameter('slug')]
        public string $slug;
        #[FromRouteParameter('title')]
        public string $title;
        #[FromRouteParameter('tags')]
        public array $tags;
        #[FromRouteParameter('nested')]
        public NestedData $nested;
    };

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('id')->once()->andReturns(123);
    $requestMock->expects('route')->with('slug')->once()->andReturns('foo-bar');
    $requestMock->expects('route')->with('title')->once()->andReturns('Foo Bar');
    $requestMock->expects('route')->with('tags')->once()->andReturns(['foo', 'bar']);
    $requestMock->expects('route')->with('nested')->once()->andReturns(['simple' => ['string' => 'baz']]);
    $requestMock->expects('toArray')->andReturns([]);

    $data = $dataClass::from($requestMock);

    expect($data->id)->toEqual(123);
    expect($data->slug)->toEqual('foo-bar');
    expect($data->title)->toEqual('Foo Bar');
    expect($data->tags)->toEqual(['foo', 'bar']);
    expect($data->nested->simple->string)->toEqual('baz');
});

it('can fill data properties from route parameter properties', function () {
    $dataClass = new class () extends Data {
        #[FromRouteParameterProperty('foo')]
        public int $id;
        #[FromRouteParameterProperty('bar')]
        public string $name;
        #[FromRouteParameterProperty('baz')]
        public string $description;
    };

    $foo = new class () extends Model {
        protected $attributes = [
            'id' => 123,
        ];
    };

    $bar = ['name' => 'Baz'];

    $baz = (object) ['description' => 'The bazzest bazz there is'];

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('foo')->once()->andReturns($foo);
    $requestMock->expects('route')->with('bar')->once()->andReturns($bar);
    $requestMock->expects('route')->with('baz')->once()->andReturns($baz);
    $requestMock->expects('toArray')->andReturns([]);

    $data = $dataClass::from($requestMock);

    expect($data->id)->toEqual(123);
    expect($data->name)->toEqual('Baz');
    expect($data->description)->toEqual('The bazzest bazz there is');
});

it('can fill data properties from route parameters using custom property mapping ', function () {
    $dataClass = new class () extends Data {
        #[FromRouteParameterProperty('something', 'name')]
        public string $title;
        #[FromRouteParameterProperty('something', 'nested.foo')]
        public string $foo;
        #[FromRouteParameterProperty('something', 'tags.0')]
        public string $tag;
        #[FromRouteParameterProperty('something', 'rows.*.total')]
        public array $totals;

        #[FromRouteParameter('user_id')]
        #[MapInputName('user_id')]
        public int $userId;
    };

    $something = [
        'name' => 'Something',
        'nested' => [
            'foo' => 'bar',
        ],
        'tags' => ['foo', 'bar'],
        'rows' => [
            ['total' => 10],
            ['total' => 20],
            ['total' => 30],
        ],
    ];

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('something')->times(4)->andReturns($something);
    $requestMock->expects('route')->with('user_id')->once()->andReturns(1);
    $requestMock->expects('toArray')->andReturns([]);

    $data = $dataClass::from($requestMock);

    expect($data->title)->toEqual('Something');
    expect($data->foo)->toEqual('bar');
    expect($data->tag)->toEqual('foo');
    expect($data->totals)->toEqual([10, 20, 30]);
    expect($data->userId)->toEqual(1);
});

it('replaces properties when route parameter properties exist', function () {
    $dataClass = new class () extends Data {
        #[FromRouteParameter('foo')]
        public string $name;
        #[FromRouteParameterProperty('bar')]
        public string $slug;

        #[FromRouteParameter('user_id')]
        #[MapInputName('user_id')]
        public int $userId;
    };

    $foo = 'Foo Lighters';
    $bar = ['slug' => 'foo-lighters'];

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('foo')->once()->andReturns($foo);
    $requestMock->expects('route')->with('bar')->once()->andReturns($bar);
    $requestMock->expects('route')->with('user_id')->once()->andReturns(2);
    $requestMock->expects('toArray')->andReturns(['name' => 'Loo Cleaners', 'slug' => 'loo-cleaners', 'user_id' => 1]);

    $data = $dataClass::from($requestMock);

    expect($data->name)->toEqual('Foo Lighters');
    expect($data->slug)->toEqual('foo-lighters');
    expect($data->userId)->toEqual(2);
});

it('skips replacing properties when route parameter properties exist and replacing is disabled', function () {
    $dataClass = new class () extends Data {
        #[FromRouteParameter('foo', replaceWhenPresentInBody: false)]
        public string $name;
        #[FromRouteParameterProperty('bar', replaceWhenPresentInBody: false)]
        public string $slug;
    };

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('foo')->never();
    $requestMock->expects('route')->with('bar')->never();
    $requestMock->expects('toArray')->andReturns(['name' => 'Loo Cleaners', 'slug' => 'loo-cleaners']);

    $data = $dataClass::from($requestMock);

    expect($data->name)->toEqual('Loo Cleaners');
    expect($data->slug)->toEqual('loo-cleaners');
});

it('skips properties it cannot find a route parameter for', function () {
    $dataClass = new class () extends Data {
        #[FromRouteParameter('foo')]
        public string $name;
        #[FromRouteParameterProperty('bar')]
        public ?string $slug = 'default-slug';
    };

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('foo')->once()->andReturnNull();
    $requestMock->expects('route')->with('bar')->once()->andReturnNull();
    $requestMock->expects('toArray')->andReturns(['name' => 'Moo Makers']);

    $data = $dataClass::from($requestMock);

    expect($data->name)->toEqual('Moo Makers');
    expect($data->slug)->toEqual('default-slug');
});

it('throws when trying to fill from a route parameter that has a scalar value', function () {
    $dataClass = new class () extends Data {
        #[FromRouteParameterProperty('foo', 'bar')]
        public string $name;
    };

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('foo')->once()->andReturn('baz');
    $requestMock->expects('toArray')->andReturns([]);

    $dataClass::from($requestMock);
})->throws(CannotFillFromRouteParameterPropertyUsingScalarValue::class);
