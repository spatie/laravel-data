<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use function Pest\Laravel\mock;

use Spatie\LaravelData\Attributes\FromRouteParameter;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Tests\Fakes\NestedData;

it('can fill data properties from scalar route parameters', function () {
    $dataClass = new class () extends Data {
        #[FromRouteParameter('id')]
        public int $id;
        #[FromRouteParameter('slug')]
        public string $slug;
        #[FromRouteParameter('title', 'long')]
        public ?string $title;
    };

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('id')->once()->andReturns(123);
    $requestMock->expects('route')->with('slug')->once()->andReturns('foo-bar');
    $requestMock->expects('route')->with('title')->once()->andReturns('Foo Bar');
    $requestMock->expects('toArray')->andReturns([]);

    $data = $dataClass::from($requestMock);

    expect($data->id)->toEqual(123);
    expect($data->slug)->toEqual('foo-bar');
    expect($data->title)->toEqual(null);
});

it('can fill data properties from non-scalar route properties (models, objects, arrays)', function () {
    $dataClass = new class () extends Data {
        #[FromRouteParameter('foo')]
        public int $id;
        #[FromRouteParameter('bar')]
        public string $name;
        #[FromRouteParameter('baz')]
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

it('can fill data properties from a route parameter using custom property mapping ', function () {
    $dataClass = new class () extends Data {
        #[FromRouteParameter('something', 'name')]
        public string $title;
        #[FromRouteParameter('something', 'nested.foo')]
        public string $foo;
        #[FromRouteParameter('something', 'tags.0')]
        public string $tag;
        #[FromRouteParameter('something', 'rows.*.total')]
        public array $totals;
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
    $requestMock->expects('toArray')->andReturns([]);

    $data = $dataClass::from($requestMock);

    expect($data->title)->toEqual('Something');
    expect($data->foo)->toEqual('bar');
    expect($data->tag)->toEqual('foo');
    expect($data->totals)->toEqual([10, 20, 30]);
});

it('can fill data properties with non-scalar route parameters ', function () {
    $dataClass = new class () extends Data {
        #[FromRouteParameter('tags', false)]
        public array $tags;
        #[FromRouteParameter('nested', false)]
        public NestedData $nested;
    };

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('tags')->once()->andReturns(['foo', 'bar']);
    $requestMock->expects('route')->with('nested')->once()->andReturns(['simple' => ['string' => 'baz']]);
    $requestMock->expects('toArray')->andReturns([]);

    $data = $dataClass::from($requestMock);

    expect($data->tags)->toEqual(['foo', 'bar']);
    expect($data->nested->simple->string)->toEqual('baz');
});

it('replaces properties when route parameter properties exist', function () {
    $dataClass = new class () extends Data {
        #[FromRouteParameter('something')]
        public string $name;
    };

    $something = ['name' => 'Best'];

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('something')->once()->andReturns($something);
    $requestMock->expects('toArray')->andReturns(['title' => 'Better']);

    $data = $dataClass::from($requestMock);

    expect($data->name)->toEqual('Best');
});

it('skips replacing properties when route parameter properties exist and replacing is disabled', function () {
    $dataClass = new class () extends Data {
        #[FromRouteParameter('something', replaceWhenPresentInBody: false)]
        public string $name;
        #[FromRouteParameter('something', 'long', false)]
        public string $description;
    };

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('something')->never();
    $requestMock->expects('toArray')->andReturns(['name' => 'Better', 'description' => 'Description']);

    $data = $dataClass::from($requestMock);

    expect($data->name)->toEqual('Better');
    expect($data->description)->toEqual('Description');
});

it('skips properties it cannot find a route parameter for', function () {
    $dataClass = new class () extends Data {
        #[FromRouteParameter('something')]
        public string $name;
    };

    $requestMock = mock(Request::class);
    $requestMock->expects('route')->with('something')->once()->andReturnNull();
    $requestMock->expects('toArray')->andReturns(['name' => 'Better']);

    $data = $dataClass::from($requestMock);

    expect($data->name)->toEqual('Better');
});
