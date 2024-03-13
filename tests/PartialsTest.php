<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Resolvers\RequestQueryStringPartialsResolver;
use Spatie\LaravelData\Support\Partials\Partial;
use Spatie\LaravelData\Support\Partials\PartialsCollection;
use Spatie\LaravelData\Support\Partials\PartialType;
use Spatie\LaravelData\Tests\Fakes\CircData;
use Spatie\LaravelData\Tests\Fakes\DefaultLazyData;
use Spatie\LaravelData\Tests\Fakes\DummyDto;
use Spatie\LaravelData\Tests\Fakes\ExceptData;
use Spatie\LaravelData\Tests\Fakes\LazyData;
use Spatie\LaravelData\Tests\Fakes\Models\FakeModel;
use Spatie\LaravelData\Tests\Fakes\MultiData;
use Spatie\LaravelData\Tests\Fakes\MultiLazyData;
use Spatie\LaravelData\Tests\Fakes\NestedLazyData;
use Spatie\LaravelData\Tests\Fakes\OnlyData;
use Spatie\LaravelData\Tests\Fakes\PartialClassConditionalData;
use Spatie\LaravelData\Tests\Fakes\SimpleChildDataWithMappedOutputName;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedOutputName;
use Spatie\LaravelData\Tests\Fakes\UlarData;

/**
 * @note these are more "special" partial tests, like including via request, conditions, ...
 *       for unit tests of the partials themselves, see VisibleDataFieldsResolverTest
 */

it('can dynamically include data based upon the request', function () {
    LazyData::setAllowedIncludes([]);

    $response = LazyData::from('Ruben')->toResponse(request());

    expect($response)->getData(true)->toBe([]);

    LazyData::setAllowedIncludes(['name']);

    $includedResponse = LazyData::from('Ruben')->toResponse(request()->merge([
        'include' => 'name',
    ]));

    expect($includedResponse)->getData(true)
        ->toMatchArray(['name' => 'Ruben']);
});

it('can disabled including data dynamically from the request', function () {
    LazyData::setAllowedIncludes([]);

    $response = LazyData::from('Ruben')->toResponse(request()->merge([
        'include' => 'name',
    ]));

    expect($response->getData(true))->toBe([]);

    LazyData::setAllowedIncludes(['name']);

    $response = LazyData::from('Ruben')->toResponse(request()->merge([
        'include' => 'name',
    ]));

    expect($response->getData(true))->toMatchArray(['name' => 'Ruben']);

    LazyData::setAllowedIncludes(null);

    $response = LazyData::from('Ruben')->toResponse(request()->merge([
        'include' => 'name',
    ]));

    expect($response->getData(true))->toMatchArray(['name' => 'Ruben']);
});

it('can dynamically exclude data based upon the request', function () {
    DefaultLazyData::setAllowedExcludes([]);

    $response = DefaultLazyData::from('Ruben')->toResponse(request());

    expect($response->getData(true))->toMatchArray(['name' => 'Ruben']);

    DefaultLazyData::setAllowedExcludes(['name']);

    $excludedResponse = DefaultLazyData::from('Ruben')->toResponse(request()->merge([
        'exclude' => 'name',
    ]));

    expect($excludedResponse->getData(true))->toBe([]);
});

it('can disable excluding data dynamically from the request', function () {
    DefaultLazyData::setAllowedExcludes([]);

    $response = DefaultLazyData::from('Ruben')->toResponse(request()->merge([
        'exclude' => 'name',
    ]));

    expect($response->getData(true))->toMatchArray(['name' => 'Ruben']);

    DefaultLazyData::setAllowedExcludes(['name']);

    $response = DefaultLazyData::from('Ruben')->toResponse(request()->merge([
        'exclude' => 'name',
    ]));

    expect($response->getData(true))->toBe([]);

    DefaultLazyData::setAllowedExcludes(null);

    $response = DefaultLazyData::from('Ruben')->toResponse(request()->merge([
        'exclude' => 'name',
    ]));

    expect($response->getData(true))->toBe([]);
});

it('can disable only data dynamically from the request', function () {
    OnlyData::setAllowedOnly([]);

    $response = OnlyData::from([
        'first_name' => 'Ruben',
        'last_name' => 'Van Assche',
    ])->toResponse(request()->merge([
        'only' => 'first_name',
    ]));

    expect($response->getData(true))->toBe([
        'first_name' => 'Ruben',
        'last_name' => 'Van Assche',
    ]);

    OnlyData::setAllowedOnly(['first_name']);

    $response = OnlyData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche'])->toResponse(request()->merge([
        'only' => 'first_name',
    ]));

    expect($response->getData(true))->toMatchArray([
        'first_name' => 'Ruben',
    ]);

    OnlyData::setAllowedOnly(null);

    $response = OnlyData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche'])->toResponse(request()->merge([
        'only' => 'first_name',
    ]));

    expect($response->getData(true))->toMatchArray([
        'first_name' => 'Ruben',
    ]);
});

it('can disable except data dynamically from the request', function () {
    ExceptData::setAllowedExcept([]);

    $response = ExceptData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche'])->toResponse(request()->merge([
        'except' => 'first_name',
    ]));

    expect($response->getData(true))->toMatchArray([
        'first_name' => 'Ruben',
        'last_name' => 'Van Assche',
    ]);

    ExceptData::setAllowedExcept(['first_name']);

    $response = ExceptData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche'])->toResponse(request()->merge([
        'except' => 'first_name',
    ]));

    expect($response->getData(true))->toMatchArray([
        'last_name' => 'Van Assche',
    ]);

    ExceptData::setAllowedExcept(null);

    $response = ExceptData::from(['first_name' => 'Ruben', 'last_name' => 'Van Assche'])->toResponse(request()->merge([
        'except' => 'first_name',
    ]));

    expect($response->getData(true))->toMatchArray([
        'last_name' => 'Van Assche',
    ]);
});


it('can conditionally include', function () {
    expect(
        MultiLazyData::from(DummyDto::rick())->includeWhen('artist', false)->toArray()
    )->toBeEmpty();

    expect(
        MultiLazyData::from(DummyDto::rick())
            ->includeWhen('artist', true)
            ->toArray()
    )
        ->toMatchArray([
            'artist' => 'Rick Astley',
        ]);

    expect(
        MultiLazyData::from(DummyDto::rick())
            ->includeWhen('name', fn (MultiLazyData $data) => $data->artist->resolve() === 'Rick Astley')
            ->toArray()
    )
        ->toMatchArray([
            'name' => 'Never gonna give you up',
        ]);
});

it('can conditionally include nested', function () {
    $data = new class () extends Data {
        public NestedLazyData $nested;
    };

    $data->nested = NestedLazyData::from('Hello World');

    expect($data->toArray())->toMatchArray(['nested' => []]);

    expect($data->includeWhen('nested.simple', true)->toArray())
        ->toMatchArray([
            'nested' => ['simple' => ['string' => 'Hello World']],
        ]);
});

it('can conditionally include using class defaults', function () {
    PartialClassConditionalData::setDefinitions(includeDefinitions: [
        'string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::createLazy(enabled: false))
        ->toArray()
        ->toMatchArray(['enabled' => false]);

    expect(PartialClassConditionalData::createLazy(enabled: true))
        ->toArray()
        ->toMatchArray(['enabled' => true, 'string' => 'Hello World']);
});

it('can conditionally include using class defaults nested', function () {
    PartialClassConditionalData::setDefinitions(includeDefinitions: [
        'nested.string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::createLazy(enabled: true))
        ->toArray()
        ->toMatchArray(['enabled' => true, 'nested' => ['string' => 'Hello World']]);
});


it('can conditionally include using class defaults multiple', function () {
    PartialClassConditionalData::setDefinitions(includeDefinitions: [
        'nested.string' => fn (PartialClassConditionalData $data) => $data->enabled,
        'string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::createLazy(enabled: false))
        ->toArray()
        ->toMatchArray(['enabled' => false]);

    expect(PartialClassConditionalData::createLazy(enabled: true))
        ->toArray()
        ->toMatchArray([
            'enabled' => true,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);
});

it('can conditionally exclude', function () {
    $data = new MultiLazyData(
        Lazy::create(fn () => 'Rick Astley')->defaultIncluded(),
        Lazy::create(fn () => 'Never gonna give you up')->defaultIncluded(),
        1989
    );

    expect((clone $data)->exceptWhen('artist', false)->toArray())
        ->toMatchArray([
            'artist' => 'Rick Astley',
            'name' => 'Never gonna give you up',
            'year' => 1989,
        ]);

    expect((clone $data)->exceptWhen('artist', true)->toArray())
        ->toMatchArray([
            'name' => 'Never gonna give you up',
            'year' => 1989,
        ]);

    expect(
        (clone $data)
            ->exceptWhen('name', fn (MultiLazyData $data) => $data->artist->resolve() === 'Rick Astley')
            ->toArray()
    )
        ->toMatchArray([
            'artist' => 'Rick Astley',
            'year' => 1989,
        ]);
});

it('can conditionally exclude nested', function () {
    $data = new class () extends Data {
        public NestedLazyData $nested;
    };

    $data->nested = new NestedLazyData(Lazy::create(fn () => SimpleData::from('Hello World'))->defaultIncluded());

    expect($data->toArray())->toMatchArray([
        'nested' => ['simple' => ['string' => 'Hello World']],
    ]);

    expect($data->exceptWhen('nested.simple', true)->toArray())
        ->toMatchArray(['nested' => []]);
});

it('can conditionally exclude using class defaults', function () {
    PartialClassConditionalData::setDefinitions(excludeDefinitions: [
        'string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::createDefaultIncluded(enabled: false))
        ->toArray()
        ->toMatchArray([
            'enabled' => false,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);

    expect(PartialClassConditionalData::createDefaultIncluded(enabled: true))
        ->toArray()
        ->toMatchArray([
            'enabled' => true,
            'nested' => ['string' => 'Hello World'],
        ]);
});

it('can conditionally exclude using class defaults nested', function () {
    PartialClassConditionalData::setDefinitions(excludeDefinitions: [
        'nested.string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::createDefaultIncluded(enabled: false))
        ->toArray()
        ->toMatchArray([
            'enabled' => false,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);

    expect(PartialClassConditionalData::createDefaultIncluded(enabled: true))
        ->toArray()
        ->toMatchArray([
            'enabled' => true,
            'string' => 'Hello World',
        ]);
});

it('can conditionally exclude using multiple class defaults', function () {
    PartialClassConditionalData::setDefinitions(excludeDefinitions: [
        'string' => fn (PartialClassConditionalData $data) => $data->enabled,
        'nested.string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::createDefaultIncluded(enabled: false))
        ->toArray()
        ->toMatchArray([
            'enabled' => false,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);

    expect(PartialClassConditionalData::createDefaultIncluded(enabled: true))
        ->toArray()
        ->toMatchArray(['enabled' => true]);
});

it('can conditionally define only', function () {
    $data = new MultiData('Hello', 'World');

    expect(
        (clone $data)->onlyWhen('first', true)->toArray()
    )
        ->toMatchArray([
            'first' => 'Hello',
        ]);

    expect(
        (clone $data)->onlyWhen('first', false)->toArray()
    )
        ->toMatchArray([
            'first' => 'Hello',
            'second' => 'World',
        ]);

    expect(
        (clone $data)
            ->onlyWhen('second', fn (MultiData $data) => $data->second === 'World')
            ->toArray()
    )
        ->toMatchArray(['second' => 'World']);

    expect(
        (clone $data)
            ->onlyWhen('first', fn (MultiData $data) => $data->first === 'Hello')
            ->onlyWhen('second', fn (MultiData $data) => $data->second === 'World')
            ->toArray()
    )
        ->toMatchArray([
            'first' => 'Hello',
            'second' => 'World',
        ]);
});

it('can conditionally define only nested', function () {
    $data = new class () extends Data {
        public MultiData $nested;
    };

    $data->nested = new MultiData('Hello', 'World');

    expect(
        (clone $data)->onlyWhen('nested.first', true)->toArray()
    )->toMatchArray([
        'nested' => ['first' => 'Hello'],
    ]);

    expect(
        (clone $data)->onlyWhen('nested.{first, second}', true)->toArray()
    )->toMatchArray([
        'nested' => [
            'first' => 'Hello',
            'second' => 'World',
        ],
    ]);
});

it('can conditionally define only using class defaults', function () {
    PartialClassConditionalData::setDefinitions(onlyDefinitions: [
        'string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::create(enabled: false))
        ->toArray()
        ->toMatchArray([
            'enabled' => false,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);

    expect(PartialClassConditionalData::create(enabled: true))
        ->toArray()
        ->toMatchArray(['string' => 'Hello World']);
});

it('can conditionally define only using class defaults nested', function () {
    PartialClassConditionalData::setDefinitions(onlyDefinitions: [
        'nested.string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::create(enabled: false))
        ->toArray()
        ->toMatchArray([
            'enabled' => false,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);

    expect(PartialClassConditionalData::create(enabled: true))
        ->toArray()
        ->toMatchArray([
            'nested' => ['string' => 'Hello World'],
        ]);
});

it('can conditionally define only using multiple class defaults', function () {
    PartialClassConditionalData::setDefinitions(onlyDefinitions: [
        'string' => fn (PartialClassConditionalData $data) => $data->enabled,
        'nested.string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::create(enabled: false))
        ->toArray()
        ->toMatchArray([
            'enabled' => false,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);

    expect(PartialClassConditionalData::create(enabled: true))
        ->toArray()
        ->toMatchArray([
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);
});

it('can conditionally define except', function () {
    $data = new MultiData('Hello', 'World');

    expect((clone $data)->exceptWhen('first', true))
        ->toArray()
        ->toMatchArray(['second' => 'World']);

    expect((clone $data)->exceptWhen('first', false))
        ->toArray()
        ->toMatchArray([
            'first' => 'Hello',
            'second' => 'World',
        ]);

    expect(
        (clone $data)
            ->exceptWhen('second', fn (MultiData $data) => $data->second === 'World')
    )
        ->toArray()
        ->toMatchArray([
            'first' => 'Hello',
        ]);

    expect(
        (clone $data)
            ->exceptWhen('first', fn (MultiData $data) => $data->first === 'Hello')
            ->exceptWhen('second', fn (MultiData $data) => $data->second === 'World')
            ->toArray()
    )->toBeEmpty();
});

it('can conditionally define except nested', function () {
    $data = new class () extends Data {
        public MultiData $nested;
    };

    $data->nested = new MultiData('Hello', 'World');

    expect((clone $data)->exceptWhen('nested.first', true))
        ->toArray()
        ->toMatchArray(['nested' => ['second' => 'World']]);

    expect((clone $data)->exceptWhen('nested.{first, second}', true))
        ->toArray()
        ->toMatchArray(['nested' => []]);
});

it('can conditionally define except using class defaults', function () {
    PartialClassConditionalData::setDefinitions(exceptDefinitions: [
        'string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::create(enabled: false))
        ->toArray()
        ->toMatchArray([
            'enabled' => false,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);

    expect(PartialClassConditionalData::create(enabled: true))
        ->toArray()
        ->toMatchArray([
            'enabled' => true,
            'nested' => ['string' => 'Hello World'],
        ]);
});

it('can conditionally define except using class defaults nested', function () {
    PartialClassConditionalData::setDefinitions(exceptDefinitions: [
        'nested.string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::create(enabled: false))
        ->toArray()
        ->toMatchArray([
            'enabled' => false,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);

    expect(PartialClassConditionalData::create(enabled: true))
        ->toArray()
        ->toMatchArray([
            'enabled' => true,
            'string' => 'Hello World',
            'nested' => [],
        ]);
});

it('can conditionally define except using multiple class defaults', function () {
    PartialClassConditionalData::setDefinitions(exceptDefinitions: [
        'string' => fn (PartialClassConditionalData $data) => $data->enabled,
        'nested.string' => fn (PartialClassConditionalData $data) => $data->enabled,
    ]);

    expect(PartialClassConditionalData::create(enabled: false))
        ->toArray()
        ->toMatchArray([
            'enabled' => false,
            'string' => 'Hello World',
            'nested' => ['string' => 'Hello World'],
        ]);

    expect(PartialClassConditionalData::create(enabled: true))
        ->toArray()
        ->toMatchArray([
            'enabled' => true,
            'nested' => [],
        ]);
});

it('can perform only and except on array properties', function () {
    $data = new class ('Hello World', ['string' => 'Hello World', 'int' => 42]) extends Data {
        public function __construct(
            public string $string,
            public array $array
        ) {
        }
    };

    expect((clone $data)->only('string', 'array.int'))
        ->toArray()
        ->toMatchArray([
            'string' => 'Hello World',
            'array' => ['int' => 42],
        ]);

    expect((clone $data)->except('string', 'array.int'))
        ->toArray()
        ->toMatchArray([
            'array' => ['string' => 'Hello World'],
        ]);
});


it('can fetch lazy properties like regular properties within PHP', function () {

    $dataClass = new class () extends Data {
        public int $id;

        public SimpleData|Lazy $simple;

        #[DataCollectionOf(SimpleData::class)]
        public DataCollection|Lazy $dataCollection;

        public FakeModel|Lazy $fakeModel;
    };

    $data = $dataClass::from([
        'id' => 42,
        'simple' => Lazy::create(fn () => SimpleData::from('A')),
        'dataCollection' => Lazy::create(fn () => SimpleData::collect(['B', 'C'], DataCollection::class)),
        'fakeModel' => Lazy::create(fn () => FakeModel::factory()->create([
            'string' => 'lazy',
        ])),
    ]);

    expect($data->id)->toBe(42);
    expect($data->simple->string)->toBe('A');
    expect($data->dataCollection->toCollection()->pluck('string')->toArray())->toBe(['B', 'C']);
    expect($data->fakeModel->string)->toBe('lazy');
});

it('has array access and will replicate partials (collection)', function () {
    $collection = MultiData::collect([
        new MultiData('first', 'second'),
    ], DataCollection::class)->only('second');

    expect($collection[0]->toArray())->toEqual(['second' => 'second']);
});

it('can dynamically include data based upon the request (collection)', function () {
    LazyData::setAllowedIncludes(['']);

    $response = (new DataCollection(LazyData::class, ['Ruben', 'Freek', 'Brent']))->toResponse(request());

    expect($response)->getData(true)
        ->toMatchArray([
            [],
            [],
            [],
        ]);

    LazyData::setAllowedIncludes(['name']);

    $includedResponse = (new DataCollection(LazyData::class, ['Ruben', 'Freek', 'Brent']))->toResponse(request()->merge([
        'include' => 'name',
    ]));

    expect($includedResponse)->getData(true)
        ->toMatchArray([
            ['name' => 'Ruben'],
            ['name' => 'Freek'],
            ['name' => 'Brent'],
        ]);
});

it('can disable manually including data in the request (collection)', function () {
    LazyData::setAllowedIncludes([]);

    $response = (new DataCollection(LazyData::class, ['Ruben', 'Freek', 'Brent']))->toResponse(request()->merge([
        'include' => 'name',
    ]));

    expect($response)->getData(true)
        ->toMatchArray([
            [],
            [],
            [],
        ]);

    LazyData::setAllowedIncludes(['name']);

    $response = (new DataCollection(LazyData::class, ['Ruben', 'Freek', 'Brent']))->toResponse(request()->merge([
        'include' => 'name',
    ]));

    expect($response)->getData(true)
        ->toMatchArray([
            ['name' => 'Ruben'],
            ['name' => 'Freek'],
            ['name' => 'Brent'],
        ]);

    LazyData::setAllowedIncludes(null);

    $response = (new DataCollection(LazyData::class, ['Ruben', 'Freek', 'Brent']))->toResponse(request()->merge([
        'include' => 'name',
    ]));

    expect($response)->getData(true)
        ->toMatchArray([
            ['name' => 'Ruben'],
            ['name' => 'Freek'],
            ['name' => 'Brent'],
        ]);
});

it('can dynamically exclude data based upon the request (collection)', function () {
    DefaultLazyData::setAllowedExcludes([]);

    $response = (new DataCollection(DefaultLazyData::class, ['Ruben', 'Freek', 'Brent']))->toResponse(request());

    expect($response)->getData(true)
        ->toMatchArray([
            ['name' => 'Ruben'],
            ['name' => 'Freek'],
            ['name' => 'Brent'],
        ]);

    DefaultLazyData::setAllowedExcludes(['name']);

    $excludedResponse = (new DataCollection(DefaultLazyData::class, ['Ruben', 'Freek', 'Brent']))->toResponse(request()->merge([
        'exclude' => 'name',
    ]));

    expect($excludedResponse)->getData(true)
        ->toMatchArray([
            [],
            [],
            [],
        ]);
});

it('can disable manually excluding data in the request (collection)', function () {
    DefaultLazyData::setAllowedExcludes([]);

    $response = (new DataCollection(DefaultLazyData::class, ['Ruben', 'Freek', 'Brent']))->toResponse(request()->merge([
        'exclude' => 'name',
    ]));

    expect($response)->getData(true)
        ->toMatchArray([
            ['name' => 'Ruben'],
            ['name' => 'Freek'],
            ['name' => 'Brent'],
        ]);

    DefaultLazyData::setAllowedExcludes(['name']);

    $response = (new DataCollection(DefaultLazyData::class, ['Ruben', 'Freek', 'Brent']))->toResponse(request()->merge([
        'exclude' => 'name',
    ]));

    expect($response)->getData(true)
        ->toMatchArray([
            [],
            [],
            [],
        ]);

    DefaultLazyData::setAllowedExcludes(null);

    $response = (new DataCollection(DefaultLazyData::class, ['Ruben', 'Freek', 'Brent']))->toResponse(request()->merge([
        'exclude' => 'name',
    ]));

    expect($response)->getData(true)
        ->toMatchArray([
            [],
            [],
            [],
        ]);
});

it('can work with lazy array data collections', function () {
    $dataClass = new class () extends Data {
        #[DataCollectionOf(SimpleData::class)]
        public Lazy|array $lazyCollection;

        #[DataCollectionOf(NestedLazyData::class)]
        public Lazy|array $nestedLazyCollection;
    };

    $dataClass->lazyCollection = Lazy::create(fn () => [
        SimpleData::from('A'),
        SimpleData::from('B'),
    ]);

    $dataClass->nestedLazyCollection = Lazy::create(fn () => [
        NestedLazyData::from('C'),
        NestedLazyData::from('D'),
    ]);

    expect($dataClass->toArray())->toMatchArray([]);

    expect($dataClass->include('lazyCollection')->toArray())->toMatchArray([
        'lazyCollection' => [
            ['string' => 'A'],
            ['string' => 'B'],
        ],
    ]);

    expect($dataClass->include('lazyCollection', 'nestedLazyCollection.simple')->toArray())->toMatchArray([
        'lazyCollection' => [
            ['string' => 'A'],
            ['string' => 'B'],
        ],

        'nestedLazyCollection' => [
            ['simple' => ['string' => 'C']],
            ['simple' => ['string' => 'D']],
        ],
    ]);
});

it('can work with lazy laravel data collections', function () {
    $dataClass = new class () extends Data {
        #[DataCollectionOf(SimpleData::class)]
        public Lazy|Collection $lazyCollection;

        #[DataCollectionOf(NestedLazyData::class)]
        public Lazy|Collection $nestedLazyCollection;
    };

    $dataClass->lazyCollection = Lazy::create(fn () => collect([
        SimpleData::from('A'),
        SimpleData::from('B'),
    ]));

    $dataClass->nestedLazyCollection = Lazy::create(fn () => collect([
        NestedLazyData::from('C'),
        NestedLazyData::from('D'),
    ]));

    expect($dataClass->toArray())->toMatchArray([]);

    expect($dataClass->include('lazyCollection')->toArray())->toMatchArray([
        'lazyCollection' => [
            ['string' => 'A'],
            ['string' => 'B'],
        ],
    ]);

    expect($dataClass->include('lazyCollection', 'nestedLazyCollection.simple')->toArray())->toMatchArray([
        'lazyCollection' => [
            ['string' => 'A'],
            ['string' => 'B'],
        ],

        'nestedLazyCollection' => [
            ['simple' => ['string' => 'C']],
            ['simple' => ['string' => 'D']],
        ],
    ]);
});

it('can work with lazy laravel data paginators', function () {
    $dataClass = new class () extends Data {
        #[DataCollectionOf(SimpleData::class)]
        public Lazy|Collection $lazyCollection;

        #[DataCollectionOf(NestedLazyData::class)]
        public Lazy|Collection $nestedLazyCollection;
    };

    $dataClass->lazyCollection = Lazy::create(fn () => new LengthAwarePaginator([
        SimpleData::from('A'),
        SimpleData::from('B'),
    ], total: 15, perPage: 15));

    $dataClass->nestedLazyCollection = Lazy::create(fn () => new LengthAwarePaginator([
        NestedLazyData::from('C'),
        NestedLazyData::from('D'),
    ], total: 15, perPage: 15));

    expect($dataClass->toArray())->toMatchArray([]);


    $array = $dataClass->include('lazyCollection')->toArray();

    expect($array['lazyCollection']['data'])->toMatchArray([
        ['string' => 'A'],
        ['string' => 'B'],
    ]);
    expect($array)->not()->toHaveKey('nestedLazyCollection');

    $array = $dataClass->include('lazyCollection', 'nestedLazyCollection.simple')->toArray();

    expect($array['lazyCollection']['data'])->toMatchArray([
        ['string' => 'A'],
        ['string' => 'B'],
    ]);
    expect($array['nestedLazyCollection']['data'])->toMatchArray([
        ['simple' => ['string' => 'C']],
        ['simple' => ['string' => 'D']],
    ]);
});

it('partials are always reset when transforming again', function () {
    $dataClass = new class (Lazy::create(fn () => NestedLazyData::from('Hello World'))) extends Data {
        public function __construct(
            public Lazy|NestedLazyData $nested
        ) {
        }
    };

    expect($dataClass->include('nested.simple')->toArray())->toBe([
        'nested' => ['simple' => ['string' => 'Hello World']],
    ]);

    expect($dataClass->include('nested')->toArray())->toBe([
        'nested' => [],
    ]);

    expect($dataClass->include()->toArray())->toBeEmpty();
});

it('can define permanent partials which will always be used', function () {
    $dataClass = new class (
        Lazy::create(fn () => NestedLazyData::from('Hello World')),
        Lazy::create(fn () => 'Hello World'),
    ) extends Data {
        public function __construct(
            public Lazy|NestedLazyData $nested,
            public Lazy|string $string,
        ) {
        }

        protected function includeProperties(): array
        {
            return [
                'nested.simple',
            ];
        }
    };

    expect($dataClass->toArray())->toBe([
        'nested' => ['simple' => ['string' => 'Hello World']],
    ]);

    expect($dataClass->include('string')->toArray())->toBe([
        'nested' => ['simple' => ['string' => 'Hello World']],
        'string' => 'Hello World',
    ]);

    expect($dataClass->toArray())->toBe([
        'nested' => ['simple' => ['string' => 'Hello World']],
    ]);
});

it('can define permanent partials using function call', function (
    Data $data,
    Closure $temporaryPartial,
    Closure $permanentPartial,
    array $expectedFullPayload,
    array $expectedPartialPayload
) {
    $data = $temporaryPartial($data);

    expect($data->toArray())->toBe($expectedPartialPayload);
    expect($data->toArray())->toBe($expectedFullPayload);

    $data = $permanentPartial($data);

    expect($data->toArray())->toBe($expectedPartialPayload);
    expect($data->toArray())->toBe($expectedPartialPayload);
})->with(function () {
    yield [
        'data' => new LazyData(
            Lazy::create(fn () => 'Rick Astley'),
        ),
        'temporaryPartial' => fn (LazyData $data) => $data->include('name'),
        'permanentPartial' => fn (LazyData $data) => $data->includePermanently('name'),
        'expectedFullPayload' => [],
        'expectedPartialPayload' => ['name' => 'Rick Astley'],
    ];

    yield [
        'data' => new LazyData(
            Lazy::create(fn () => 'Rick Astley')->defaultIncluded(),
        ),
        'temporaryPartial' => fn (LazyData $data) => $data->exclude('name'),
        'permanentPartial' => fn (LazyData $data) => $data->excludePermanently('name'),
        'expectedFullPayload' => ['name' => 'Rick Astley'],
        'expectedPartialPayload' => [],
    ];

    yield [
        'data' => new MultiData(
            'Rick Astley',
            'Never gonna give you up',
        ),
        'temporaryPartial' => fn (MultiData $data) => $data->only('first'),
        'permanentPartial' => fn (MultiData $data) => $data->onlyPermanently('first'),
        'expectedFullPayload' => ['first' => 'Rick Astley', 'second' => 'Never gonna give you up'],
        'expectedPartialPayload' => ['first' => 'Rick Astley'],
    ];

    yield [
        'data' => new MultiData(
            'Rick Astley',
            'Never gonna give you up',
        ),
        'temporaryPartial' => fn (MultiData $data) => $data->except('first'),
        'permanentPartial' => fn (MultiData $data) => $data->exceptPermanently('first'),
        'expectedFullPayload' => ['first' => 'Rick Astley', 'second' => 'Never gonna give you up'],
        'expectedPartialPayload' => ['second' => 'Never gonna give you up'],
    ];
});

it('can set partials on a nested data object and these will be respected', function () {
    class TestMultiLazyNestedDataWithObjectAndCollection extends Data
    {
        public function __construct(
            public Lazy|NestedLazyData $nested,
            #[DataCollectionOf(NestedLazyData::class)]
            public Lazy|array $nestedCollection,
        ) {
        }
    }

    $collection = new DataCollection(\TestMultiLazyNestedDataWithObjectAndCollection::class, [
        new \TestMultiLazyNestedDataWithObjectAndCollection(
            NestedLazyData::from('A'),
            [
                NestedLazyData::from('B1')->include('simple'),
                NestedLazyData::from('B2'),
            ],
        ),
        new \TestMultiLazyNestedDataWithObjectAndCollection(
            NestedLazyData::from('C'),
            [
                NestedLazyData::from('D1'),
                NestedLazyData::from('D2')->include('simple.string'),
            ],
        ),
    ]);

    $collection->include('nested.simple');

    $data = new class (Lazy::create(fn () => $collection)) extends Data {
        public function __construct(
            #[DataCollectionOf(\TestMultiLazyNestedDataWithObjectAndCollection::class)]
            public Lazy|DataCollection $collection
        ) {
        }
    };

    expect($data->include('collection')->toArray())->toMatchArray([
        'collection' => [
            [
                'nested' => [
                    'simple' => [
                        'string' => 'A',
                    ],
                ],
                'nestedCollection' => [
                    [
                        'simple' => [
                            'string' => 'B1',
                        ],
                    ],
                    [],
                ],
            ],
            [
                'nested' => [
                    'simple' => [
                        'string' => 'C',
                    ],
                ],
                'nestedCollection' => [
                    [],
                    [
                        'simple' => [
                            'string' => 'D2',
                        ],
                    ],
                ],
            ],
        ],
    ]);
});

it('will check if partials are valid as request partials', function (
    ?array $lazyDataAllowedIncludes,
    ?array $dataAllowedIncludes,
    ?string $includes,
    ?PartialsCollection $expectedPartials,
    array $expectedResponse
) {
    LazyData::setAllowedIncludes($lazyDataAllowedIncludes);

    $data = new class (
        Lazy::create(fn () => 'Hello'),
        Lazy::create(fn () => LazyData::from('Hello')),
        Lazy::create(fn () => LazyData::collect(['Hello', 'World'])),
    ) extends Data {
        public static ?array $allowedIncludes;

        public function __construct(
            public Lazy|string $property,
            public Lazy|LazyData $nested,
            #[DataCollectionOf(LazyData::class)]
            public Lazy|array $collection,
        ) {
        }

        public static function allowedRequestIncludes(): ?array
        {
            return static::$allowedIncludes;
        }
    };

    $data::$allowedIncludes = $dataAllowedIncludes;

    $request = request();

    if ($includes !== null) {
        $request->merge([
            'include' => $includes,
        ]);
    }

    $partials = app(RequestQueryStringPartialsResolver::class)->execute($data, $request, PartialType::Include);

    expect($partials?->toArray())->toEqual($expectedPartials?->toArray());
    expect($data->toResponse($request)->getData(assoc: true))->toEqual($expectedResponse);
})->with(function () {
    yield 'disallowed property inclusion' => [
        'lazyDataAllowedIncludes' => [],
        'dataAllowedIncludes' => [],
        'includes' => 'property',
        'expectedPartials' => PartialsCollection::create(),
        'expectedResponse' => [],
    ];

    yield 'allowed property inclusion' => [
        'lazyDataAllowedIncludes' => [],
        'dataAllowedIncludes' => ['property'],
        'includes' => 'property',
        'expectedPartials' => PartialsCollection::create(
            Partial::create('property'),
        ),
        'expectedResponse' => [
            'property' => 'Hello',
        ],
    ];

    yield 'allowed data property inclusion without nesting' => [
        'lazyDataAllowedIncludes' => [],
        'dataAllowedIncludes' => ['nested'],
        'includes' => 'nested.name',
        'expectedPartials' => PartialsCollection::create(
            Partial::create('nested'),
        ),
        'expectedResponse' => [
            'nested' => [],
        ],
    ];

    yield 'allowed data property inclusion with nesting' => [
        'lazyDataAllowedIncludes' => ['name'],
        'dataAllowedIncludes' => ['nested'],
        'includes' => 'nested.name',
        'expectedPartials' => PartialsCollection::create(
            Partial::create('nested.name'),
        ),
        'expectedResponse' => [
            'nested' => [
                'name' => 'Hello',
            ],
        ],
    ];

    yield 'allowed data collection property inclusion without nesting' => [
        'lazyDataAllowedIncludes' => [],
        'dataAllowedIncludes' => ['collection'],
        'includes' => 'collection.name',
        'expectedPartials' => PartialsCollection::create(
            Partial::create('collection'),
        ),
        'expectedResponse' => [
            'collection' => [
                [],
                [],
            ],
        ],
    ];

    yield 'allowed data collection property inclusion with nesting' => [
        'lazyDataAllowedIncludes' => ['name'],
        'dataAllowedIncludes' => ['collection'],
        'includes' => 'collection.name',
        'expectedPartials' => PartialsCollection::create(
            Partial::create('collection.name'),
        ),
        'expectedResponse' => [
            'collection' => [
                ['name' => 'Hello'],
                ['name' => 'World'],
            ],
        ],
    ];

    yield 'allowed nested data property inclusion without defining allowed includes on nested' => [
        'lazyDataAllowedIncludes' => null,
        'dataAllowedIncludes' => ['nested'],
        'includes' => 'nested.name',
        'expectedPartials' => PartialsCollection::create(
            Partial::create('nested.name'),
        ),
        'expectedResponse' => [
            'nested' => [
                'name' => 'Hello',
            ],
        ],
    ];

    yield 'allowed all nested data property inclusion without defining allowed includes on nested' => [
        'lazyDataAllowedIncludes' => null,
        'dataAllowedIncludes' => ['nested'],
        'includes' => 'nested.*',
        'expectedPartials' => PartialsCollection::create(
            Partial::create('nested.*'),
        ),
        'expectedResponse' => [
            'nested' => [
                'name' => 'Hello',
            ],
        ],
    ];

    yield 'disallowed all nested data property inclusion ' => [
        'lazyDataAllowedIncludes' => [],
        'dataAllowedIncludes' => ['nested'],
        'includes' => 'nested.*',
        'expectedPartials' => PartialsCollection::create(
            Partial::create('nested'),
        ),
        'expectedResponse' => [
            'nested' => [],
        ],
    ];

    yield 'multi property inclusion' => [
        'lazyDataAllowedIncludes' => null,
        'dataAllowedIncludes' => ['nested', 'property'],
        'includes' => 'nested.*,property',
        'expectedPartials' => PartialsCollection::create(
            Partial::create('nested.*'),
            Partial::create('property'),
        ),
        'expectedResponse' => [
            'property' => 'Hello',
            'nested' => [
                'name' => 'Hello',
            ],
        ],
    ];

    yield 'without property inclusion' => [
        'lazyDataAllowedIncludes' => null,
        'dataAllowedIncludes' => ['nested', 'property'],
        'includes' => null,
        'expectedPartials' => null,
        'expectedResponse' => [],
    ];

    yield 'with invalid partial definition' => [
        'lazyDataAllowedIncludes' => null,
        'dataAllowedIncludes' => null,
        'includes' => '',
        'expectedPartials' => PartialsCollection::create(),
        'expectedResponse' => [],
    ];

    yield 'with non existing field' => [
        'lazyDataAllowedIncludes' => [],
        'dataAllowedIncludes' => [],
        'includes' => 'non-existing',
        'expectedPartials' => PartialsCollection::create(),
        'expectedResponse' => [],
    ];

    yield 'with non existing nested field' => [
        'lazyDataAllowedIncludes' => [],
        'dataAllowedIncludes' => [],
        'includes' => 'non-existing.still-non-existing',
        'expectedPartials' => PartialsCollection::create(),
        'expectedResponse' => [],
    ];

    yield 'with non allowed nested field' => [
        'lazyDataAllowedIncludes' => [],
        'dataAllowedIncludes' => [],
        'includes' => 'nested.name',
        'expectedPartials' => PartialsCollection::create(),
        'expectedResponse' => [],
    ];

    yield 'with non allowed nested all' => [
        'lazyDataAllowedIncludes' => [],
        'dataAllowedIncludes' => [],
        'includes' => 'nested.*',
        'expectedPartials' => PartialsCollection::create(),
        'expectedResponse' => [],
    ];
});

it('can combine request and manual includes', function () {
    $dataclass = new class (
        Lazy::create(fn () => 'Rick Astley'),
        Lazy::create(fn () => 'Never gonna give you up'),
        Lazy::create(fn () => 1986),
    ) extends MultiLazyData {
        public static function allowedRequestIncludes(): ?array
        {
            return null;
        }
    };

    $data = $dataclass->include('name')->toResponse(request()->merge([
        'include' => 'artist',
    ]))->getData(true);

    expect($data)->toMatchArray([
        'artist' => 'Rick Astley',
        'name' => 'Never gonna give you up',
    ]);
});

it('handles parsing includes from request in different formats', function (array $input, array $expected) {
    $dataclass = new class (
        Lazy::create(fn () => 'Rick Astley'),
        Lazy::create(fn () => 'Never gonna give you up'),
        Lazy::create(fn () => 1986),
    ) extends MultiLazyData {
        public static function allowedRequestIncludes(): ?array
        {
            return ['*'];
        }
    };

    $request = request()->merge($input);

    $data = $dataclass->toResponse($request)->getData(assoc: true);

    expect($data)->toHaveKeys($expected);
})->with(function () {
    yield 'input as array' => [
        'input' => ['include' => ['artist', 'name']],
        'expected' => ['artist', 'name'],
    ];

    yield 'input as comma separated' => [
        'input' => ['include' => 'artist,name'],
        'expected' => ['artist', 'name'],
    ];
});

it('handles partials when not transforming values by copying them to lazy nested data objects', function () {
    $dataClass = new class () extends Data {
        public Lazy|NestedLazyData $nested;

        public function __construct()
        {
            $this->nested = Lazy::create(fn () => NestedLazyData::from('Rick Astley'));
        }
    };

    expect($dataClass->include('nested.simple')->toArray())->toMatchArray([
        'nested' => [
            'simple' => [
                'string' => 'Rick Astley',
            ],
        ],
    ]);

    $nested = $dataClass->include('nested.simple')->all()['nested'];

    expect($nested)->toBeInstanceOf(NestedLazyData::class);
    expect($nested->toArray())->toMatchArray([
        'simple' => [
            'string' => 'Rick Astley',
        ],
    ]);
});

it('handles partials when not transforming values by copying them to lazy data collections', function () {
    $dataClass = new class () extends Data {
        public Lazy|DataCollection $collection;

        public function __construct()
        {
            $this->collection = Lazy::create(fn () => NestedLazyData::collect([
                'Rick Astley',
                'Jon Bon Jovi',
            ], DataCollection::class));
        }
    };

    expect($dataClass->include('collection.simple')->toArray())->toMatchArray([
        'collection' => [
            [
                'simple' => [
                    'string' => 'Rick Astley',
                ],
            ],
            [
                'simple' => [
                    'string' => 'Jon Bon Jovi',
                ],
            ],
        ],
    ]);

    $nested = $dataClass->include('collection.simple')->all()['collection'];

    expect($nested)->toBeInstanceOf(DataCollection::class);
    expect($nested->toArray())->toMatchArray([
        [
            'simple' => [
                'string' => 'Rick Astley',
            ],
        ],
        [
            'simple' => [
                'string' => 'Jon Bon Jovi',
            ],
        ],
    ]);
});

it('handles partials when not transforming values by copying them to a lazy array of data objects', function () {
    $dataClass = new class () extends Data {
        /** @var array<Spatie\LaravelData\Tests\Fakes\NestedLazyData> */
        public Lazy|array $collection;

        public function __construct()
        {
            $this->collection = Lazy::create(fn () => NestedLazyData::collect([
                'Rick Astley',
                'Jon Bon Jovi',
            ]));
        }
    };

    expect($dataClass->include('collection.simple')->toArray())->toMatchArray([
        'collection' => [
            [
                'simple' => [
                    'string' => 'Rick Astley',
                ],
            ],
            [
                'simple' => [
                    'string' => 'Jon Bon Jovi',
                ],
            ],
        ],
    ]);

    $nested = $dataClass->include('collection.simple')->all()['collection'];

    expect(array_map(fn (NestedLazyData $data) => $data->toArray(), $nested))->toMatchArray([
        [
            'simple' => [
                'string' => 'Rick Astley',
            ],
        ],
        [
            'simple' => [
                'string' => 'Jon Bon Jovi',
            ],
        ],
    ]);
});

it('handles partials when not transforming values by copying them to lazy nested data objects in data collections', function () {
    $dataClass = new class () extends Data {
        public DataCollection $collection;

        public function __construct()
        {
            $this->collection = NestedLazyData::collect([
                'Rick Astley',
                'Jon Bon Jovi',
            ], DataCollection::class);
        }
    };

    expect($dataClass->include('collection.simple')->toArray())->toMatchArray([
        'collection' => [
            [
                'simple' => [
                    'string' => 'Rick Astley',
                ],
            ],
            [
                'simple' => [
                    'string' => 'Jon Bon Jovi',
                ],
            ],
        ],
    ]);

    $nested = $dataClass->include('collection.simple')->all()['collection'];

    expect($nested)->toBeInstanceOf(DataCollection::class);
    expect($nested->toArray())->toMatchArray([
        [
            'simple' => [
                'string' => 'Rick Astley',
            ],
        ],
        [
            'simple' => [
                'string' => 'Jon Bon Jovi',
            ],
        ],
    ]);
});

it('handles parsing except from request with mapped output name', function () {
    $dataclass = SimpleDataWithMappedOutputName::from([
        'id' => 1,
        'amount' => 1000,
        'any_string' => 'test',
        'child' => SimpleChildDataWithMappedOutputName::from([
            'id' => 2,
            'amount' => 2000,
        ]),
    ]);

    $request = request()->merge(['except' => ['paid_amount', 'any_string', 'child.child_amount']]);

    $data = $dataclass->toResponse($request)->getData(assoc: true);

    expect($data)->toMatchArray([
        'id' => 1,
        'child' => [
            'id' => 2,
        ],
    ]);
});

it('handles circular dependencies', function () {
    $dataClass = new CircData(
        'test',
        new UlarData(
            'test',
            new CircData('test', null)
        )
    );

    $data = $dataClass->toResponse(request())->getData(assoc: true);

    expect($data)->toBe([
        'string' => 'test',
        'ular' => [
            'string' => 'test',
            'circ' => [
                'string' => 'test',
                'ular' => null,
            ],
        ],
    ]);
    // Not really a test with expectation, we just want to check we don't end up in an infinite loop
});
