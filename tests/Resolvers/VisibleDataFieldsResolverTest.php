<?php

use Inertia\LazyProp;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Hidden;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Resolvers\VisibleDataFieldsResolver;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\Lazy\ClosureLazy;
use Spatie\LaravelData\Support\Lazy\InertiaLazy;
use Spatie\LaravelData\Support\Partials\ResolvedPartial;
use Spatie\LaravelData\Support\Partials\ResolvedPartialsCollection;
use Spatie\LaravelData\Support\Partials\Segments\AllPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\FieldsPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\NestedPartialSegment;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;
use Spatie\LaravelData\Tests\Fakes\FakeModelData;
use Spatie\LaravelData\Tests\Fakes\FakeNestedModelData;
use Spatie\LaravelData\Tests\Fakes\Models\FakeNestedModel;

function findVisibleFields(
    Data $data,
    TransformationContextFactory $contextFactory,
): array {
    return app(VisibleDataFieldsResolver::class)->execute(
        $data,
        app(DataConfig::class)->getDataClass($data::class),
        $contextFactory->get($data)
    );
}

it('will hide hidden fields', function () {
    $dataClass = new class () extends Data {
        public string $visible = 'visible';

        #[Hidden]
        public string $hidden = 'hidden';
    };

    expect(findVisibleFields($dataClass, TransformationContextFactory::create()))->toEqual([
        'visible' => null,
    ]);

    expect($dataClass->toArray())->toBe([
        'visible' => 'visible',
    ]);
});

it('will hide fields which are uninitialized', function () {
    $dataClass = new class () extends Data {
        public string $visible = 'visible';

        public Optional|string $optional;
    };

    expect(findVisibleFields($dataClass, TransformationContextFactory::create()))->toEqual([
        'visible' => null,
    ]);

    expect($dataClass->toArray())->toBe([
        'visible' => 'visible',
    ]);
});

it('will hide optional values', function () {
    $dataClass = new class () extends Data {
        public Lazy|string|Optional $lazyOptional;

        public function __construct(
            public string $visible = 'visible',
            public Optional|string $optional = new Optional(),
        ) {
            $this->lazyOptional = Lazy::create(fn () => new Optional());
        }
    };

    expect(findVisibleFields($dataClass, TransformationContextFactory::create()))->toEqual([
        'visible' => null,
    ]);

    expect($dataClass->include('lazyOptional')->toArray())->toBe([
        'visible' => 'visible',
    ]);
});

it('will always show non-lazy values when no only or exclude operations are performed on it', function () {
    $dataClass = new class () extends Data {
        public function __construct(
            public string $visible = 'visible',
            public Lazy|string $lazy = 'lazy but visible',
        ) {
        }
    };

    expect(findVisibleFields($dataClass, TransformationContextFactory::create()))->toEqual([
        'visible' => null,
        'lazy' => null,
    ]);

    expect($dataClass->toArray())->toBe([
        'visible' => 'visible',
        'lazy' => 'lazy but visible',
    ]);
});

it('can have lazy behaviour based upon a condition', function () {
    $dataClass = new class () extends Data {
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

    expect(findVisibleFields($dataClass, TransformationContextFactory::create()))->toEqual([
        'name' => null,
    ]);

    expect($dataClass::create('Freek')->toArray())->toBe([]);
    expect($dataClass::create('Ruben')->toArray())->toMatchArray(['name' => 'Ruben']);
});

it('is impossible to lazy include conditional lazy properties', function () {
    $dataClass = new class () extends Data {
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

    $data = $dataClass::create('Freek')->include('name')->toArray();

    expect($data)->toBeEmpty();
});

it('can include data based upon relations being loaded', function () {
    $model = FakeNestedModel::factory()->create();

    $transformed = FakeNestedModelData::createWithLazyWhenLoaded($model)->all();

    expect($transformed)->not()->toHaveKey('fake_model');

    $transformed = FakeNestedModelData::createWithLazyWhenLoaded($model->load('fakeModel'))->all();

    expect($transformed)
        ->toHaveKey('fake_model')
        ->and($transformed['fake_model'])->toBeInstanceOf(FakeModelData::class);
});

it('can include data based upon relations loaded when they are null', function () {
    $model = FakeNestedModel::factory(['fake_model_id' => null])->create();

    $transformed = FakeNestedModelData::createWithLazyWhenLoaded($model)->all();

    expect($transformed)->not()->toHaveKey('fake_model');

    $transformed = FakeNestedModelData::createWithLazyWhenLoaded($model->load('fakeModel'))->all();

    expect($transformed)
        ->toHaveKey('fake_model')
        ->and($transformed['fake_model'])->toBeNull();
});

it('can include lazy data by default', function () {
    $dataClass = new class ('') extends Data {
        public function __construct(
            public string|Lazy $name
        ) {
        }

        public static function create(string $name): static
        {
            return new self(
                Lazy::create(fn () => $name)->defaultIncluded()
            );
        }
    };

    expect($dataClass::create('Ruben')->toArray())->toMatchArray(['name' => 'Ruben']);
});

it('always transforms lazy inertia data to inertia lazy props', function () {
    $dataClass = new class () extends Data {
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

    expect($dataClass::create('Freek')->toArray()['name'])->toBeInstanceOf(LazyProp::class);
});

it('always transforms closure lazy into closures for inertia', function () {
    $dataClass = new class () extends Data {
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

    expect($dataClass::create('Freek')->toArray()['name'])->toBeInstanceOf(Closure::class);
});

class VisibleFieldsSingleData extends Data
{
    public function __construct(
        public string $string,
        public int $int
    ) {
    }

    public static function instance(): self
    {
        return new self('hello', 42);
    }
}

class VisibleFieldsNestedData extends Data
{
    public function __construct(
        public VisibleFieldsSingleData $a,
        public VisibleFieldsSingleData $b,
    ) {
    }

    public static function instance(): self
    {
        return new self(
            VisibleFieldsSingleData::instance(),
            VisibleFieldsSingleData::instance(),
        );
    }
}

class VisibleFieldsData extends Data
{
    public function __construct(
        public string $string,
        public int $int,
        public VisibleFieldsSingleData $single,
        public VisibleFieldsNestedData $nested,
        #[DataCollectionOf(VisibleFieldsSingleData::class)]
        public array $collection,
    ) {
    }

    public static function instance(): self
    {
        return new self(
            'hello',
            42,
            VisibleFieldsSingleData::instance(),
            VisibleFieldsNestedData::instance(),
            [
                VisibleFieldsSingleData::instance(),
                VisibleFieldsSingleData::instance(),
            ],
        );
    }
}


it('can execute excepts', function (
    TransformationContextFactory $factory,
    array $expectedVisibleFields,
    array $expectedTransformed
) {
    $data = VisibleFieldsData::instance();

    $visibleFields = findVisibleFields($data, $factory);

    $visibleFields = array_map(fn ($field) => $field instanceof TransformationContext ? $field->toArray() : $field, $visibleFields);
    $expectedVisibleFields = array_map(fn ($field) => $field instanceof TransformationContext ? $field->toArray() : $field, $expectedVisibleFields);

    expect($visibleFields)->toEqual($expectedVisibleFields);

    expect($data->transform($factory))->toEqual($expectedTransformed);
})->with(function () {
    yield 'single field' => [
        'factory' => TransformationContextFactory::create()
            ->except('single'),
        'fields' => [
            'string' => null,
            'int' => null,
            'nested' => new TransformationContext(),
            'collection' => new TransformationContext(),
        ],
        'transformed' => [
            'string' => 'hello',
            'int' => 42,
            'nested' => [
                'a' => ['string' => 'hello', 'int' => 42],
                'b' => ['string' => 'hello', 'int' => 42],
            ],
            'collection' => [
                ['string' => 'hello', 'int' => 42],
                ['string' => 'hello', 'int' => 42],
            ],
        ],
    ];

    yield 'multiple fields' => [
        'factory' => TransformationContextFactory::create()
            ->except('{string,int,single}'),
        'fields' => [
            'nested' => new TransformationContext(),
            'collection' => new TransformationContext(),
        ],
        'transformed' => [
            'nested' => [
                'a' => ['string' => 'hello', 'int' => 42],
                'b' => ['string' => 'hello', 'int' => 42],
            ],
            'collection' => [
                ['string' => 'hello', 'int' => 42],
                ['string' => 'hello', 'int' => 42],
            ],
        ],
    ];

    yield 'all' => [
        'factory' => TransformationContextFactory::create()
            ->except('*'),
        'fields' => [],
        'transformed' => [],
    ];

    yield 'nested data object single field' => [
        'factory' => TransformationContextFactory::create()
            ->except('string', 'int', 'single', 'collection') // ignore non nested object fields
            ->except('nested.a'),
        'fields' => [
            'nested' => new TransformationContext(
                exceptPartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('nested'), new FieldsPartialSegment(['a'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'nested' => [
                'b' => ['string' => 'hello', 'int' => 42],
            ],
        ],
    ];

    yield 'nested data object multiple fields' => [
        'factory' => TransformationContextFactory::create()
            ->except('string', 'int', 'single', 'collection') // ignore non nested object fields
            ->except('nested.{a,b}'),
        'fields' => [
            'nested' => new TransformationContext(
                exceptPartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('nested'), new FieldsPartialSegment(['a', 'b'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'nested' => [],
        ],
    ];

    yield 'nested data object all' => [
        'factory' => TransformationContextFactory::create()
            ->except('string', 'int', 'single', 'collection') // ignore non nested object fields
            ->except('nested.*'),
        'fields' => [
            'nested' => new TransformationContext(
                exceptPartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('nested'), new AllPartialSegment()], 1)
                ),
            ),
        ],
        'transformed' => [
            'nested' => [],
        ],
    ];

    yield 'nested data collectable single field' => [
        'factory' => TransformationContextFactory::create()
            ->except('string', 'int', 'single', 'nested') // ignore non collection fields
            ->except('collection.string'),
        'fields' => [
            'collection' => new TransformationContext(
                exceptPartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('collection'), new FieldsPartialSegment(['string'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'collection' => [
                ['int' => 42],
                ['int' => 42],
            ],
        ],
    ];

    yield 'nested data collectable multiple fields' => [
        'factory' => TransformationContextFactory::create()
            ->except('string', 'int', 'single', 'nested') // ignore non collection fields
            ->except('collection.{string,int}'),
        'fields' => [
            'collection' => new TransformationContext(
                exceptPartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('collection'), new FieldsPartialSegment(['string', 'int'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'collection' => [
                [],
                [],
            ],
        ],
    ];

    yield 'nested data collectable all' => [
        'factory' => TransformationContextFactory::create()
            ->except('string', 'int', 'single', 'nested') // ignore non collection fields
            ->except('collection.*'),
        'fields' => [
            'collection' => new TransformationContext(
                exceptPartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('collection'), new AllPartialSegment()], 1)
                ),
            ),
        ],
        'transformed' => [
            'collection' => [
                [],
                [],
            ],
        ],
    ];

    yield 'combination' => [
        'factory' => TransformationContextFactory::create()
            ->except('string', 'int', 'single.string')
            ->except('collection.string')
            ->except('nested.a.string'),
        'fields' => [
            'single' => new TransformationContext(
                exceptPartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('single'), new FieldsPartialSegment(['string'])], 1)
                ),
            ),
            'collection' => new TransformationContext(
                exceptPartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('collection'), new FieldsPartialSegment(['string'])], 1)
                ),
            ),
            'nested' => new TransformationContext(
                exceptPartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('nested'), new NestedPartialSegment('a'), new FieldsPartialSegment(['string'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'single' => ['int' => 42],
            'collection' => [
                ['int' => 42],
                ['int' => 42],
            ],
            'nested' => [
                'a' => ['int' => 42],
                'b' => ['string' => 'hello', 'int' => 42],
            ],
        ],
    ];
});

it("can execute only's", function (
    TransformationContextFactory $factory,
    array $expectedVisibleFields,
    array $expectedTransformed
) {
    $data = VisibleFieldsData::instance();

    $visibleFields = findVisibleFields($data, $factory);

    $visibleFields = array_map(fn ($field) => $field instanceof TransformationContext ? $field->toArray() : $field, $visibleFields);
    $expectedVisibleFields = array_map(fn ($field) => $field instanceof TransformationContext ? $field->toArray() : $field, $expectedVisibleFields);

    expect($visibleFields)->toEqual($expectedVisibleFields);

    expect($data->transform($factory))->toEqual($expectedTransformed);
})->with(function () {
    yield 'single field' => [
        'factory' => TransformationContextFactory::create()
            ->only('single'),
        'fields' => [
            'single' => new TransformationContext(),
        ],
        'transformed' => [
            'single' => ['string' => 'hello', 'int' => 42,],
        ],
    ];

    yield 'multiple fields' => [
        'factory' => TransformationContextFactory::create()
            ->only('{string,int,single}'),
        'fields' => [
            'string' => null,
            'int' => null,
            'single' => new TransformationContext(),
        ],
        'transformed' => [
            'string' => 'hello',
            'int' => 42,
            'single' => ['string' => 'hello', 'int' => 42,],
        ],
    ];

    yield 'all' => [
        'factory' => TransformationContextFactory::create()
            ->only('*'),
        'fields' => [
            'string' => null,
            'int' => null,
            'single' => new TransformationContext(),
            'nested' => new TransformationContext(),
            'collection' => new TransformationContext(),
        ],
        'transformed' => [
            'string' => 'hello',
            'int' => 42,
            'single' => ['string' => 'hello', 'int' => 42,],
            'nested' => [
                'a' => ['string' => 'hello', 'int' => 42],
                'b' => ['string' => 'hello', 'int' => 42],
            ],
            'collection' => [
                ['string' => 'hello', 'int' => 42],
                ['string' => 'hello', 'int' => 42],
            ],
        ],
    ];

    yield 'nested data object single field' => [
        'factory' => TransformationContextFactory::create()
            ->only('nested.a'),
        'fields' => [
            'nested' => new TransformationContext(
                onlyPartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('nested'), new FieldsPartialSegment(['a'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'nested' => [
                'a' => ['string' => 'hello', 'int' => 42],
            ],
        ],
    ];

    yield 'nested data object multiple fields' => [
        'factory' => TransformationContextFactory::create()
            ->only('nested.{a,b}'),
        'fields' => [
            'nested' => new TransformationContext(
                onlyPartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('nested'), new FieldsPartialSegment(['a', 'b'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'nested' => [
                'a' => ['string' => 'hello', 'int' => 42],
                'b' => ['string' => 'hello', 'int' => 42],
            ],
        ],
    ];

    yield 'nested data object all' => [
        'factory' => TransformationContextFactory::create()
            ->only('nested.*'),
        'fields' => [
            'nested' => new TransformationContext(
                onlyPartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('nested'), new AllPartialSegment()], 1)
                ),
            ),
        ],
        'transformed' => [
            'nested' => [
                'a' => ['string' => 'hello', 'int' => 42],
                'b' => ['string' => 'hello', 'int' => 42],
            ],
        ],
    ];

    yield 'nested data collectable single field' => [
        'factory' => TransformationContextFactory::create()
            ->only('collection.string'),
        'fields' => [
            'collection' => new TransformationContext(
                onlyPartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('collection'), new FieldsPartialSegment(['string'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'collection' => [
                ['string' => 'hello'],
                ['string' => 'hello'],
            ],
        ],
    ];

    yield 'nested data collectable multiple fields' => [
        'factory' => TransformationContextFactory::create()
            ->only('collection.{string,int}'),
        'fields' => [
            'collection' => new TransformationContext(
                onlyPartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('collection'), new FieldsPartialSegment(['string', 'int'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'collection' => [
                ['string' => 'hello', 'int' => 42],
                ['string' => 'hello', 'int' => 42],
            ],
        ],
    ];

    yield 'nested data collectable all' => [
        'factory' => TransformationContextFactory::create()
            ->only('collection.*'),
        'fields' => [
            'collection' => new TransformationContext(
                onlyPartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('collection'), new AllPartialSegment()], 1)
                ),
            ),
        ],
        'transformed' => [
            'collection' => [
                ['string' => 'hello', 'int' => 42],
                ['string' => 'hello', 'int' => 42],
            ],
        ],
    ];

    yield 'combination' => [
        'factory' => TransformationContextFactory::create()
            ->only('string', 'single.string')
            ->only('collection.string')
            ->only('nested.a.string'),
        'fields' => [
            'string' => null,
            'single' => new TransformationContext(
                onlyPartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('single'), new FieldsPartialSegment(['string'])], 1)
                ),
            ),
            'collection' => new TransformationContext(
                onlyPartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('collection'), new FieldsPartialSegment(['string'])], 1)
                ),
            ),
            'nested' => new TransformationContext(
                onlyPartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('nested'), new NestedPartialSegment('a'), new FieldsPartialSegment(['string'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'string' => 'hello',
            'single' => ['string' => 'hello'],
            'collection' => [
                ['string' => 'hello'],
                ['string' => 'hello'],
            ],
            'nested' => [
                'a' => ['string' => 'hello'],
            ],
        ],
    ];
});

class LazyVisibleFieldsSingleData extends Data
{
    public function __construct(
        public Lazy|string $string,
        public Lazy|int $int
    ) {
    }

    public static function instance(bool $includeByDefault): self
    {
        return new self(
            Lazy::create(fn () => 'hello')->defaultIncluded($includeByDefault),
            Lazy::create(fn () => 42)->defaultIncluded($includeByDefault)
        );
    }
}

class LazyVisibleFieldsNestedData extends Data
{
    public function __construct(
        public Lazy|LazyVisibleFieldsSingleData $a,
        public Lazy|LazyVisibleFieldsSingleData $b,
    ) {
    }

    public static function instance(bool $includeByDefault): self
    {
        return new self(
            Lazy::create(fn () => LazyVisibleFieldsSingleData::instance($includeByDefault))->defaultIncluded($includeByDefault),
            Lazy::create(fn () => LazyVisibleFieldsSingleData::instance($includeByDefault))->defaultIncluded($includeByDefault),
        );
    }
}

class LazyVisibleFieldsData extends Data
{
    public function __construct(
        public Lazy|string $string,
        public Lazy|int $int,
        public Lazy|LazyVisibleFieldsSingleData $single,
        public Lazy|LazyVisibleFieldsNestedData $nested,
        #[DataCollectionOf(LazyVisibleFieldsSingleData::class)]
        public Lazy|array $collection,
    ) {
    }

    public static function instance(bool $includeByDefault): self
    {
        return new self(
            Lazy::create(fn () => 'hello')->defaultIncluded($includeByDefault),
            Lazy::create(fn () => 42)->defaultIncluded($includeByDefault),
            Lazy::create(fn () => LazyVisibleFieldsSingleData::instance($includeByDefault))->defaultIncluded($includeByDefault),
            Lazy::create(fn () => LazyVisibleFieldsNestedData::instance($includeByDefault))->defaultIncluded($includeByDefault),
            Lazy::create(fn () => [
                LazyVisibleFieldsSingleData::instance($includeByDefault),
                LazyVisibleFieldsSingleData::instance($includeByDefault),
            ])->defaultIncluded($includeByDefault),
        );
    }
}

it('can execute includes', function (
    TransformationContextFactory $factory,
    array $expectedVisibleFields,
    array $expectedTransformed
) {
    $data = LazyVisibleFieldsData::instance(false);

    $visibleFields = findVisibleFields($data, $factory);

    $visibleFields = array_map(fn ($field) => $field instanceof TransformationContext ? $field->toArray() : $field, $visibleFields);
    $expectedVisibleFields = array_map(fn ($field) => $field instanceof TransformationContext ? $field->toArray() : $field, $expectedVisibleFields);

    expect($visibleFields)->toEqual($expectedVisibleFields);

    expect($data->transform($factory))->toEqual($expectedTransformed);
})->with(function () {
    yield 'single field' => [
        'factory' => TransformationContextFactory::create()
            ->include('single'),
        'fields' => [
            'single' => new TransformationContext(),
        ],
        'transformed' => [
            'single' => [],
        ],
    ];

    yield 'multiple fields' => [
        'factory' => TransformationContextFactory::create()
            ->include('{string,int,single}'),
        'fields' => [
            'single' => new TransformationContext(),
            'int' => null,
            'string' => null,
        ],
        'transformed' => [
            'single' => [],
            'int' => 42,
            'string' => 'hello',
        ],
    ];

    yield 'all' => [
        'factory' => TransformationContextFactory::create()
            ->include('*'),
        'fields' => [
            'single' => new TransformationContext(
                includePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new AllPartialSegment()], 3)
                ),
            ),
            'int' => null,
            'string' => null,
            'nested' => new TransformationContext(
                includePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new AllPartialSegment()], 3)
                ),
            ),
            'collection' => new TransformationContext(
                includePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new AllPartialSegment()], 3)
                ),
            ),
        ],
        'transformed' => [
            'single' => ['string' => 'hello', 'int' => 42,],
            'int' => 42,
            'string' => 'hello',
            'nested' => [
                'a' => ['string' => 'hello', 'int' => 42],
                'b' => ['string' => 'hello', 'int' => 42],
            ],
            'collection' => [
                ['string' => 'hello', 'int' => 42],
                ['string' => 'hello', 'int' => 42],
            ],
        ],
    ];

    yield 'nested data object single field' => [
        'factory' => TransformationContextFactory::create()
            ->only('nested') // ignore non nested object fields
            ->include('nested.a'),
        'fields' => [
            'nested' => new TransformationContext(
                includePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('nested'), new FieldsPartialSegment(['a'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'nested' => [
                'a' => [],
            ],
        ],
    ];

    yield 'nested data object multiple fields' => [
        'factory' => TransformationContextFactory::create()
            ->only('nested') // ignore non nested object fields
            ->include('nested.{a,b}'),
        'fields' => [
            'nested' => new TransformationContext(
                includePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('nested'), new FieldsPartialSegment(['a', 'b'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'nested' => [
                'a' => [],
                'b' => [],
            ],
        ],
    ];

    yield 'nested data object all' => [
        'factory' => TransformationContextFactory::create()
            ->only('nested') // ignore non nested object fields
            ->include('nested.*'),
        'fields' => [
            'nested' => new TransformationContext(
                includePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('nested'), new AllPartialSegment()], 1)
                ),
            ),
        ],
        'transformed' => [
            'nested' => [
                'a' => ['string' => 'hello', 'int' => 42],
                'b' => ['string' => 'hello', 'int' => 42],
            ],
        ],
    ];

    yield 'nested data object deep nesting' => [
        'factory' => TransformationContextFactory::create()
            ->only('nested') // ignore non nested object fields
            ->include('nested.a.string', 'nested.b.int'),
        'fields' => [
            'nested' => new TransformationContext(
                includePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('nested'), new NestedPartialSegment('a'), new FieldsPartialSegment(['string'])], 1),
                    new ResolvedPartial([new NestedPartialSegment('nested'), new NestedPartialSegment('b'), new FieldsPartialSegment(['int'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'nested' => [
                'a' => ['string' => 'hello'],
                'b' => ['int' => 42],
            ],
        ],
    ];

    yield 'nested data collectable single field' => [
        'factory' => TransformationContextFactory::create()
            ->only('collection') // ignore non collection fields
            ->include('collection.string'),
        'fields' => [
            'collection' => new TransformationContext(
                includePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('collection'), new FieldsPartialSegment(['string'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'collection' => [
                ['string' => 'hello'],
                ['string' => 'hello'],
            ],
        ],
    ];

    yield 'nested data collectable multiple fields' => [
        'factory' => TransformationContextFactory::create()
            ->only('collection') // ignore non collection fields
            ->include('collection.{string,int}'),
        'fields' => [
            'collection' => new TransformationContext(
                includePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('collection'), new FieldsPartialSegment(['string', 'int'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'collection' => [
                ['string' => 'hello', 'int' => 42],
                ['string' => 'hello', 'int' => 42],
            ],
        ],
    ];

    yield 'nested data collectable all' => [
        'factory' => TransformationContextFactory::create()
            ->only('collection') // ignore non collection fields
            ->include('collection.*'),
        'fields' => [
            'collection' => new TransformationContext(
                includePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('collection'), new AllPartialSegment()], 1)
                ),
            ),
        ],
        'transformed' => [
            'collection' => [
                ['string' => 'hello', 'int' => 42],
                ['string' => 'hello', 'int' => 42],
            ],
        ],
    ];

    yield 'combination' => [
        'factory' => TransformationContextFactory::create()
            ->include('string', 'single.string')
            ->include('collection.string')
            ->include('nested.a.string'),
        'fields' => [
            'string' => null,
            'single' => new TransformationContext(
                includePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('single'), new FieldsPartialSegment(['string'])], 1)
                ),
            ),
            'collection' => new TransformationContext(
                includePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('collection'), new FieldsPartialSegment(['string'])], 1)
                ),
            ),
            'nested' => new TransformationContext(
                includePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('nested'), new NestedPartialSegment('a'), new FieldsPartialSegment(['string'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'string' => 'hello',
            'single' => ['string' => 'hello'],
            'collection' => [
                ['string' => 'hello'],
                ['string' => 'hello'],
            ],
            'nested' => [
                'a' => ['string' => 'hello'],
            ],
        ],
    ];
});

it('can execute excludes', function (
    TransformationContextFactory $factory,
    array $expectedVisibleFields,
    array $expectedTransformed
) {
    $data = LazyVisibleFieldsData::instance(true);

    $visibleFields = findVisibleFields($data, $factory);

    $visibleFields = array_map(fn ($field) => $field instanceof TransformationContext ? $field->toArray() : $field, $visibleFields);
    $expectedVisibleFields = array_map(fn ($field) => $field instanceof TransformationContext ? $field->toArray() : $field, $expectedVisibleFields);

    expect($visibleFields)->toEqual($expectedVisibleFields);

    expect($data->transform($factory))->toEqual($expectedTransformed);
})->with(function () {
    yield 'single field' => [
        'factory' => TransformationContextFactory::create()
            ->exclude('single'),
        'fields' => [
            'string' => null,
            'int' => null,
            'nested' => new TransformationContext(),
            'collection' => new TransformationContext(),
        ],
        'transformed' => [
            'string' => 'hello',
            'int' => 42,
            'nested' => [
                'a' => ['string' => 'hello', 'int' => 42],
                'b' => ['string' => 'hello', 'int' => 42],
            ],
            'collection' => [
                ['string' => 'hello', 'int' => 42],
                ['string' => 'hello', 'int' => 42],
            ],
        ],
    ];

    yield 'multiple fields' => [
        'factory' => TransformationContextFactory::create()
            ->exclude('{string,int,single}'),
        'fields' => [
            'nested' => new TransformationContext(),
            'collection' => new TransformationContext(),
        ],
        'transformed' => [
            'nested' => [
                'a' => ['string' => 'hello', 'int' => 42],
                'b' => ['string' => 'hello', 'int' => 42],
            ],
            'collection' => [
                ['string' => 'hello', 'int' => 42],
                ['string' => 'hello', 'int' => 42],
            ],
        ],
    ];

    yield 'all' => [
        'factory' => TransformationContextFactory::create()
            ->exclude('*'),
        'fields' => [],
        'transformed' => [],
    ];

    yield 'nested data object single field' => [
        'factory' => TransformationContextFactory::create()
            ->only('nested') // ignore non nested object fields
            ->exclude('nested.a'),
        'fields' => [
            'nested' => new TransformationContext(
                excludePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('nested'), new FieldsPartialSegment(['a'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'nested' => [
                'b' => ['string' => 'hello', 'int' => 42],
            ],
        ],
    ];

    yield 'nested data object multiple fields' => [
        'factory' => TransformationContextFactory::create()
            ->only('nested') // ignore non nested object fields
            ->exclude('nested.{a,b}'),
        'fields' => [
            'nested' => new TransformationContext(
                excludePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('nested'), new FieldsPartialSegment(['a', 'b'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'nested' => [],
        ],
    ];

    yield 'nested data object all' => [
        'factory' => TransformationContextFactory::create()
            ->only('nested') // ignore non nested object fields
            ->exclude('nested.*'),
        'fields' => [
            'nested' => new TransformationContext(
                excludePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('nested'), new AllPartialSegment()], 1)
                ),
            ),
        ],
        'transformed' => [
            'nested' => [],
        ],
    ];

    yield 'nested data object deep nesting' => [
        'factory' => TransformationContextFactory::create()
            ->only('nested') // ignore non nested object fields
            ->exclude('nested.a.string', 'nested.b.int'),
        'fields' => [
            'nested' => new TransformationContext(
                excludePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('nested'), new NestedPartialSegment('a'), new FieldsPartialSegment(['string'])], 1),
                    new ResolvedPartial([new NestedPartialSegment('nested'), new NestedPartialSegment('b'), new FieldsPartialSegment(['int'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'nested' => [
                'a' => ['int' => 42],
                'b' => ['string' => 'hello'],
            ],
        ],
    ];

    yield 'nested data collectable single field' => [
        'factory' => TransformationContextFactory::create()
            ->only('collection') // ignore non collection fields
            ->exclude('collection.string'),
        'fields' => [
            'collection' => new TransformationContext(
                excludePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('collection'), new FieldsPartialSegment(['string'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'collection' => [
                ['int' => 42],
                ['int' => 42],
            ],
        ],
    ];

    yield 'nested data collectable multiple fields' => [
        'factory' => TransformationContextFactory::create()
            ->only('collection') // ignore non collection fields
            ->exclude('collection.{string,int}'),
        'fields' => [
            'collection' => new TransformationContext(
                excludePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('collection'), new FieldsPartialSegment(['string', 'int'])], 1)
                ),
            ),
        ],
        'transformed' => [
            'collection' => [
                [],
                [],
            ],
        ],
    ];

    yield 'nested data collectable all' => [
        'factory' => TransformationContextFactory::create()
            ->only('collection') // ignore non collection fields
            ->exclude('collection.*'),
        'fields' => [
            'collection' => new TransformationContext(
                excludePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('collection'), new AllPartialSegment()], 1)
                ),
            ),
        ],
        'transformed' => [
            'collection' => [
                [],
                [],
            ],
        ],
    ];

    yield 'combination' => [
        'factory' => TransformationContextFactory::create()
            ->exclude('string', 'single.string')
            ->exclude('collection.string')
            ->exclude('nested.a.string'),
        'fields' => [
            'single' => new TransformationContext(
                excludePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('single'), new FieldsPartialSegment(['string'])], 1)
                ),
            ),
            'collection' => new TransformationContext(
                excludePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('collection'), new FieldsPartialSegment(['string'])], 1)
                ),
            ),
            'nested' => new TransformationContext(
                excludePartials: ResolvedPartialsCollection::create(
                    new ResolvedPartial([new NestedPartialSegment('nested'), new NestedPartialSegment('a'), new FieldsPartialSegment(['string'])], 1)
                ),
            ),
            'int' => null,
        ],
        'transformed' => [
            'single' => ['int' => 42],
            'collection' => [
                ['int' => 42],
                ['int' => 42],
            ],
            'nested' => [
                'a' => ['int' => 42],
                'b' => ['string' => 'hello', 'int' => 42],
            ],
            'int' => 42,
        ],
    ];
});

it('can combine all the partials', function () {
    $data = new LazyVisibleFieldsData(
        Lazy::create(fn () => 'hello'),
        Lazy::create(fn () => 42),
        Lazy::create(fn () => LazyVisibleFieldsSingleData::instance(true))->defaultIncluded(),
        Lazy::create(fn () => LazyVisibleFieldsNestedData::instance(false)),
        Lazy::create(fn () => [
            LazyVisibleFieldsSingleData::instance(false),
            LazyVisibleFieldsSingleData::instance(true),
        ]),
    );

    $factory = TransformationContextFactory::create()
        ->except('int', 'collection.int', 'nested.b.int')
        ->only('single.*', 'nested.*', 'collection.*', 'string')
        ->include('nested.a.string', 'nested.b.*', 'collection.string')
        ->exclude('single.int');

    $expectedVisibleFields = [
        'single' => new TransformationContext(
            excludePartials: ResolvedPartialsCollection::create(
                new ResolvedPartial([new NestedPartialSegment('single'), new FieldsPartialSegment(['int'])], 1)
            ),
            onlyPartials: ResolvedPartialsCollection::create(
                new ResolvedPartial([new NestedPartialSegment('single'), new AllPartialSegment()], 1)
            ),
        ),
        'nested' => new TransformationContext(
            includePartials: ResolvedPartialsCollection::create(
                new ResolvedPartial([new NestedPartialSegment('nested'), new NestedPartialSegment('a'), new FieldsPartialSegment(['string'])], 1),
                new ResolvedPartial([new NestedPartialSegment('nested'), new NestedPartialSegment('b'), new AllPartialSegment()], 1)
            ),
            onlyPartials: ResolvedPartialsCollection::create(
                new ResolvedPartial([new NestedPartialSegment('nested'), new AllPartialSegment()], 1)
            ),
            exceptPartials: ResolvedPartialsCollection::create(
                new ResolvedPartial([new NestedPartialSegment('nested'), new NestedPartialSegment('b'), new FieldsPartialSegment(['int'])], 1)
            ),
        ),
        'collection' => new TransformationContext(
            includePartials: ResolvedPartialsCollection::create(
                new ResolvedPartial([new NestedPartialSegment('collection'), new FieldsPartialSegment(['string'])], 1)
            ),
            onlyPartials: ResolvedPartialsCollection::create(
                new ResolvedPartial([new NestedPartialSegment('collection'), new AllPartialSegment()], 1)
            ),
            exceptPartials: ResolvedPartialsCollection::create(
                new ResolvedPartial([new NestedPartialSegment('collection'), new FieldsPartialSegment(['int'])], 1)
            ),
        ),
    ];

    $visibleFields = array_map(fn ($field) => $field instanceof TransformationContext ? $field->toArray() : $field, findVisibleFields($data, $factory));
    $expectedVisibleFields = array_map(fn ($field) => $field instanceof TransformationContext ? $field->toArray() : $field, $expectedVisibleFields);

    expect($visibleFields)->toEqual($expectedVisibleFields);

    expect($data->transform($factory))->toEqual([
        'single' => ['string' => 'hello'],
        'nested' => [
            'a' => ['string' => 'hello'],
            'b' => ['string' => 'hello'],
        ],
        'collection' => [
            ['string' => 'hello'],
            ['string' => 'hello'],
        ],
    ]);
});
