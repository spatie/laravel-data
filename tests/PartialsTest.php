<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Inertia\LazyProp;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\Lazy\ClosureLazy;
use Spatie\LaravelData\Support\Lazy\InertiaLazy;
use Spatie\LaravelData\Tests\Fakes\DefaultLazyData;
use Spatie\LaravelData\Tests\Fakes\DummyDto;
use Spatie\LaravelData\Tests\Fakes\ExceptData;
use Spatie\LaravelData\Tests\Fakes\FakeModelData;
use Spatie\LaravelData\Tests\Fakes\FakeNestedModelData;
use Spatie\LaravelData\Tests\Fakes\LazyData;
use Spatie\LaravelData\Tests\Fakes\Models\FakeModel;
use Spatie\LaravelData\Tests\Fakes\Models\FakeNestedModel;
use Spatie\LaravelData\Tests\Fakes\MultiData;
use Spatie\LaravelData\Tests\Fakes\MultiLazyData;
use Spatie\LaravelData\Tests\Fakes\NestedLazyData;
use Spatie\LaravelData\Tests\Fakes\OnlyData;
use Spatie\LaravelData\Tests\Fakes\PartialClassConditionalData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

it('can include a lazy property', function () {
    $data = new LazyData(Lazy::create(fn () => 'test'));

    expect($data->toArray())->toBe([]);

    expect($data->include('name')->toArray())
        ->toMatchArray([
            'name' => 'test',
        ]);
});

it('can have a prefilled in lazy property', function () {
    $data = new LazyData('test');

    expect($data->toArray())->toMatchArray([
        'name' => 'test',
    ]);

    expect($data->include('name')->toArray())
        ->toMatchArray([
            'name' => 'test',
        ]);
});

it('can include a nested lazy property', function () {
    class TestIncludeableNestedLazyDataProperties extends Data
    {
        public function __construct(
            public LazyData|Lazy $data,
            #[DataCollectionOf(LazyData::class)]
            public array|Lazy $collection,
        ) {
        }
    }

    $data = new \TestIncludeableNestedLazyDataProperties(
        Lazy::create(fn () => LazyData::from('Hello')),
        Lazy::create(fn () => LazyData::collect(['is', 'it', 'me', 'your', 'looking', 'for',])),
    );

    expect((clone $data)->toArray())->toBe([]);

    expect((clone $data)->include('data')->toArray())->toMatchArray([
        'data' => [],
    ]);

    expect((clone $data)->include('data.name')->toArray())->toMatchArray([
        'data' => ['name' => 'Hello'],
    ]);

    expect((clone $data)->include('collection')->toArray())->toMatchArray([
        'collection' => [
            [],
            [],
            [],
            [],
            [],
            [],
        ],
    ]);

    expect((clone $data)->include('collection.name')->toArray())->toMatchArray([
        'collection' => [
            ['name' => 'is'],
            ['name' => 'it'],
            ['name' => 'me'],
            ['name' => 'your'],
            ['name' => 'looking'],
            ['name' => 'for'],
        ],
    ]);
});

it('can include specific nested data collections', function () {
    class TestSpecificDefinedIncludeableCollectedAndNestedLazyData extends Data
    {
        public function __construct(
            #[DataCollectionOf(MultiLazyData::class)]
            public array|Lazy $songs
        ) {
        }
    }

    $collection = Lazy::create(fn () => MultiLazyData::collect([
        DummyDto::rick(),
        DummyDto::bon(),
    ]));

    $data = new \TestSpecificDefinedIncludeableCollectedAndNestedLazyData($collection);

    expect($data->include('songs.name')->toArray())->toMatchArray([
        'songs' => [
            ['name' => DummyDto::rick()->name],
            ['name' => DummyDto::bon()->name],
        ],
    ]);

    expect($data->include('songs.{name,artist}')->toArray())->toMatchArray([
        'songs' => [
            [
                'name' => DummyDto::rick()->name,
                'artist' => DummyDto::rick()->artist,
            ],
            [
                'name' => DummyDto::bon()->name,
                'artist' => DummyDto::bon()->artist,
            ],
        ],
    ]);

    expect($data->include('songs.*')->toArray())->toMatchArray([
        'songs' => [
            [
                'name' => DummyDto::rick()->name,
                'artist' => DummyDto::rick()->artist,
                'year' => DummyDto::rick()->year,
            ],
            [
                'name' => DummyDto::bon()->name,
                'artist' => DummyDto::bon()->artist,
                'year' => DummyDto::bon()->year,
            ],
        ],
    ]);
});

it('can have a conditional lazy data', function () {
    $blueprint = new class () extends Data {
        public function __construct(
            public string|Lazy|null $name = null
        ) {
        }

        public static function create(string $name): static
        {
            return new self(
                Lazy::when(fn () => $name === 'Ruben', fn () => $name)
            );
        }
    };

    $data = $blueprint::create('Freek');

    expect($data->toArray())->toBe([]);

    $data = $blueprint::create('Ruben');

    expect($data->toArray())->toMatchArray(['name' => 'Ruben']);
});

it('cannot have conditional lazy data manually loaded', function () {
    $blueprint = new class () extends Data {
        public function __construct(
            public string|Lazy|null $name = null
        ) {
        }

        public static function create(string $name): static
        {
            return new self(
                Lazy::when(fn () => $name === 'Ruben', fn () => $name)
            );
        }
    };

    $data = $blueprint::create('Freek');

    expect($data->include('name')->toArray())->toBeEmpty();
});

it('can include data based upon relations loaded', function () {
    $model = FakeNestedModel::factory()->create();

    $transformed = FakeNestedModelData::createWithLazyWhenLoaded($model)->all();

    expect($transformed)->not->toHaveKey('fake_model');

    $transformed = FakeNestedModelData::createWithLazyWhenLoaded($model->load('fakeModel'))->all();

    expect($transformed)->toHaveKey('fake_model')
        ->and($transformed['fake_model'])->toBeInstanceOf(FakeModelData::class);
});

it('can include data based upon relations loaded when they are null', function () {
    $model = FakeNestedModel::factory(['fake_model_id' => null])->create();

    $transformed = FakeNestedModelData::createWithLazyWhenLoaded($model)->all();

    expect($transformed)->not->toHaveKey('fake_model');

    $transformed = FakeNestedModelData::createWithLazyWhenLoaded($model->load('fakeModel'))->all();

    expect($transformed)->toHaveKey('fake_model')
        ->and($transformed['fake_model'])->toBeNull();
});

it('can have default included lazy data', function () {
    $data = new class ('Freek') extends Data {
        public function __construct(public string|Lazy $name)
        {
        }
    };

    expect($data->toArray())->toMatchArray(['name' => 'Freek']);
});

it('can exclude default lazy data', function () {
    $data = DefaultLazyData::from('Freek');

    expect($data->exclude('name')->toArray())->toBe([]);
});

it('always transforms lazy inertia data to inertia lazy props', function () {
    $blueprint = new class () extends Data {
        public function __construct(
            public string|InertiaLazy|null $name = null
        ) {
        }

        public static function create(string $name): static
        {
            return new self(
                Lazy::inertia(fn () => $name)
            );
        }
    };

    $data = $blueprint::create('Freek');

    expect($data->toArray()['name'])->toBeInstanceOf(LazyProp::class);
});

it('always transforms closure lazy into closures for inertia', function () {
    $blueprint = new class () extends Data {
        public function __construct(
            public string|ClosureLazy|null $name = null
        ) {
        }

        public static function create(string $name): static
        {
            return new self(
                Lazy::closure(fn () => $name)
            );
        }
    };

    $data = $blueprint::create('Freek');

    expect($data->toArray()['name'])->toBeInstanceOf(Closure::class);
});


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


it('will not include lazy optional values when transforming', function () {
    $data = new class ('Hello World', Lazy::create(fn () => Optional::create())) extends Data {
        public function __construct(
            public string $string,
            public string|Optional|Lazy $lazy_optional_string,
        ) {
        }
    };

    expect(($data)->include('lazy_optional_string')->toArray())->toMatchArray([
        'string' => 'Hello World',
    ]);
});

it('excludes optional values data', function () {
    $dataClass = new class () extends Data {
        public string|Optional $name;
    };

    $data = $dataClass::from([]);

    expect($data->toArray())->toBe([]);
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
    $dataClass = new class(
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

it('can combine multiple partials', function (
    array $include,
    array $exclude,
    array $only,
    array $except,
    array $expected
) {
    $dataClass = new class(
        Lazy::create(fn () => NestedLazyData::from('Hello World')),
        Lazy::create(fn () => NestedLazyData::collect(['Hello', 'World'])),
        Lazy::create(fn () => SimpleData::from('Hello World')),
        Lazy::create(fn () => MultiLazyData::from('Hello', 'World', 42)),
        Lazy::create(fn () => 'Hello World')->defaultIncluded(),
    ) extends Data {
        public function __construct(
            public Lazy|NestedLazyData $nested,
            #[DataCollectionOf(NestedLazyData::class)]
            public Lazy|array $collection,
            public Lazy|SimpleData $simple,
            public Lazy|MultiLazyData $multi,
            public Lazy|string $string,
        ) {
        }
    };

    $array = $dataClass->include(...$include)->exclude(...$exclude)->only(...$only)->except(...$except)->toArray();

    expect($array)->toMatchArray($expected);
})->with(function () {
    yield 'no includes' => [
        'include' => [],
        'exclude' => [],
        'only' => [],
        'except' => [],
        'expected' => [
            'string' => 'Hello World',
        ],
    ];

    yield 'include and exclude' => [
        'include' => ['simple'],
        'exclude' => ['string'],
        'only' => [],
        'except' => [],
        'expected' => [
            'simple' => ['string' => 'Hello World'],
        ],
    ];

    yield 'combined include' => [
        'include' => ['multi.*', 'simple', 'collection.*'],
        'exclude' => [],
        'only' => [],
        'except' => [],
        'expected' => [
            'collection' => [
                ['simple' => ['string' => 'Hello']],
                ['simple' => ['string' => 'World']],
            ],
            'simple' => ['string' => 'Hello World'],
            'multi' => [
                'artist' => 'Hello',
                'name' => 'World',
                'year' => 42,
            ],
            'string' => 'Hello World',
        ],
    ];

    yield 'included similar paths' => [
        'include' => ['multi.artist', 'multi.name'],
        'exclude' => [],
        'only' => [],
        'except' => [],
        'expected' => [
            'multi' => [
                'artist' => 'Hello',
                'name' => 'World',
            ],
            'string' => 'Hello World',
        ],
    ];

    yield 'include all' => [
        'include' => ['*'],
        'exclude' => [],
        'only' => [],
        'except' => [],
        'expected' => [
            'collection' => [
                ['simple' => ['string' => 'Hello']],
                ['simple' => ['string' => 'World']],
            ],
            'simple' => ['string' => 'Hello World'],
            'multi' => [
                'artist' => 'Hello',
                'name' => 'World',
                'year' => 42,
            ],
            'string' => 'Hello World',
        ],
    ];

    yield 'except and only' => [
        'include' => ['multi.*'],
        'exclude' => [],
        'only' => ['multi.{artist,name}'],
        'except' => ['multi.year'],
        'expected' => [
            'multi' => [
                'artist' => 'Hello',
                'name' => 'World',
            ],
        ],
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

    $data = new class(Lazy::create(fn () => $collection)) extends Data {
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
