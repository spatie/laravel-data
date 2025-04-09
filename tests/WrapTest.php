<?php

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\post;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withoutExceptionHandling;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Tests\Fakes\MultiNestedData;
use Spatie\LaravelData\Tests\Fakes\NestedData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithWrap;

function performRequest(string $string): TestResponse
{
    return postJson('/example-route', [
        'string' => $string,
    ]);
}

it('can wrap data objects by method call', function () {
    expect(
        SimpleData::from('Hello World')
            ->wrap('wrap')
            ->toResponse(\request())
            ->getData(true)
    )->toMatchArray(['wrap' => ['string' => 'Hello World']]);

    expect(
        SimpleData::collect(['Hello', 'World'], DataCollection::class)
            ->wrap('wrap')
            ->toResponse(\request())
            ->getData(true)
    )->toMatchArray([
        'wrap' => [
            ['string' => 'Hello'],
            ['string' => 'World'],
        ],
    ]);
});


it('can wrap data objects using a global default', function () {
    config()->set('data.wrap', 'wrap');

    expect(
        SimpleData::from('Hello World')
            ->toResponse(\request())->getData(true)
    )->toMatchArray(['wrap' => ['string' => 'Hello World']]);

    expect(
        SimpleData::from('Hello World')
            ->wrap('other-wrap')
            ->toResponse(\request())->getData(true)
    )
        ->toMatchArray(['other-wrap' => ['string' => 'Hello World']]);

    expect(
        SimpleData::from('Hello World')
            ->withoutWrapping()
            ->toResponse(\request())->getData(true)
    )
        ->toMatchArray(['string' => 'Hello World']);

    expect(
        SimpleData::collect(['Hello', 'World'], DataCollection::class)
            ->toResponse(\request())->getData(true)
    )
        ->toMatchArray([
            'wrap' => [
                ['string' => 'Hello'],
                ['string' => 'World'],
            ],
        ]);

    expect(
        SimpleData::from('Hello World')
            ->withoutWrapping()
            ->toResponse(\request())->getData(true)
    )
        ->toMatchArray(['string' => 'Hello World']);

    expect(
        (new DataCollection(SimpleData::class, ['Hello', 'World']))
            ->wrap('other-wrap')
            ->toResponse(\request())
            ->getData(true)
    )
        ->toMatchArray([
            'other-wrap' => [
                ['string' => 'Hello'],
                ['string' => 'World'],
            ],
        ]);

    expect(
        (new DataCollection(SimpleData::class, ['Hello', 'World']))
            ->withoutWrapping()
            ->toResponse(\request())->getData(true)
    )
        ->toMatchArray([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]);
});

it('can set a default wrap on a data object', function () {
    expect(
        SimpleDataWithWrap::from('Hello World')
            ->toResponse(\request())
            ->getData(true)
    )
        ->toMatchArray(['wrap' => ['string' => 'Hello World']]);

    expect(
        SimpleDataWithWrap::from('Hello World')
            ->wrap('other-wrap')
            ->toResponse(\request())->getData(true)
    )
        ->toMatchArray(['other-wrap' => ['string' => 'Hello World']]);

    expect(
        SimpleDataWithWrap::from('Hello World')
            ->withoutWrapping()
            ->toResponse(\request())->getData(true)
    )
        ->toMatchArray(['string' => 'Hello World']);
});

it('wraps additional data', function () {
    $dataClass = new class ('Hello World') extends Data {
        public function __construct(
            public string $string
        ) {
        }

        public function with(): array
        {
            return ['with' => 'this'];
        }
    };

    $data = $dataClass->additional(['additional' => 'this'])
        ->wrap('wrap')
        ->toResponse(\request())
        ->getData(true);

    expect($data)->toMatchArray([
        'wrap' => ['string' => 'Hello World'],
        'additional' => 'this',
        'with' => 'this',
    ]);
});

it('wraps complex data structures', function () {
    $data = new MultiNestedData(
        new NestedData(SimpleData::from('Hello')),
        [
            new NestedData(SimpleData::from('World')),
        ],
    );

    expect(
        $data->wrap('wrap')->toResponse(\request())->getData(true)
    )->toMatchArray([
        'wrap' => [
            'nested' => ['simple' => ['string' => 'Hello']],
            'nestedCollection' => [
                ['simple' => ['string' => 'World']],
            ],
        ],
    ]);
});

it('wraps complex data structures with a global', function () {
    config()->set('data.wrap', 'wrap');

    $data = new MultiNestedData(
        new NestedData(SimpleData::from('Hello')),
        [
            new NestedData(SimpleData::from('World')),
        ],
    );

    expect(
        $data->wrap('wrap')->toResponse(\request())->getData(true)
    )->toMatchArray([
        'wrap' => [
            'nested' => ['simple' => ['string' => 'Hello']],
            'nestedCollection' => [
                'wrap' => [
                    ['simple' => ['string' => 'World']],
                ],
            ],
        ],
    ]);
});

it('only wraps responses, default transformations will not wrap', function () {
    expect(
        SimpleData::from('Hello World')->wrap('wrap')
    )
        ->toArray()
        ->toMatchArray(['string' => 'Hello World']);

    expect(
        SimpleData::collect(['Hello', 'World'], DataCollection::class)->wrap('wrap')
    )
        ->toArray()
        ->toMatchArray([
            ['string' => 'Hello'],
            ['string' => 'World'],
        ]);
});

it('will wrap responses which are data', function () {
    Route::post('/example-route', function () {
        return SimpleData::from(request()->input('string'))->wrap('data');
    });

    performRequest('Hello World')
        ->assertCreated()
        ->assertJson(['data' => ['string' => 'Hello World']]);
});

it('will wrap responses which are data collections', function () {
    Route::post('/example-route', function () {
        return SimpleData::collect([
            request()->input('string'),
            strtoupper(request()->input('string')),
        ], DataCollection::class)->wrap('data');
    });

    performRequest('Hello World')
        ->assertCreated()
        ->assertJson([
            'data' => [
                ['string' => 'Hello World'],
                ['string' => 'HELLO WORLD'],
            ],
        ]);
});

it('check laravel functionality', function () {
    class TestEmbeddedResource extends JsonResource
    {
        public function toArray(Request $request)
        {
            return [
                'id' => $this['id'],
            ];
        }
    }

    class TestEmbeddedResourceCollection extends ResourceCollection
    {
        public $collects = TestEmbeddedResource::class;
    }

    class TestResource extends JsonResource
    {
        public function toArray(Request $request)
        {
            return [
                'id' => 1,
                'nested' => TestEmbeddedResource::make(['id' => 2]),
                'nested_collection' => TestEmbeddedResource::collection([
                    ['id' => 3],
                    ['id' => 4],
                ]),
                'nested_collection_object' => new TestEmbeddedResourceCollection([
                    ['id' => 5],
                    ['id' => 6],
                ]),
            ];
        }
    }

    class TestResourceCollection extends ResourceCollection
    {
        public $collects = TestResource::class;
    }

    Route::post('/resource', function () {
        return TestResource::make([]);
    });

    Route::post('/collection', function () {
        return new TestResourceCollection([
            [],
            [],
        ]);
    });

    withoutExceptionHandling();

    $expectedResource = [
        'id' => 1,
        'nested' => [
            'id' => 2,
        ],
        'nested_collection' => [
            ['id' => 3],
            ['id' => 4],
        ],
        'nested_collection_object' => [
            ['id' => 5],
            ['id' => 6],
        ],
    ];

    post('/resource')
        ->assertExactJson(['data' => $expectedResource]);

    post('/collection')
        ->assertExactJson([
            'data' => [
                $expectedResource,
                $expectedResource,
            ],
        ]);
});
