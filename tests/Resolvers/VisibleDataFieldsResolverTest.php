<?php

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Hidden;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Resolvers\VisibleDataFieldsResolver;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\Partials\ResolvedPartial;
use Spatie\LaravelData\Support\Partials\ResolvedPartialsCollection;
use Spatie\LaravelData\Support\Partials\Segments\AllPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\FieldsPartialSegment;
use Spatie\LaravelData\Support\Partials\Segments\NestedPartialSegment;
use Spatie\LaravelData\Support\Transformation\TransformationContext;
use Spatie\LaravelData\Support\Transformation\TransformationContextFactory;

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
});

it('will hide fields which are uninitialized', function () {
    $dataClass = new class () extends Data {
        public string $visible = 'visible';

        public Optional|string $optional;
    };

    expect(findVisibleFields($dataClass, TransformationContextFactory::create()))->toEqual([
        'visible' => null,
    ]);
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
                    new ResolvedPartial([new NestedPartialSegment('nested'), new NestedPartialSegment('a') , new FieldsPartialSegment(['string'])], 1)
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
                    new ResolvedPartial([new NestedPartialSegment('nested'), new NestedPartialSegment('a') , new FieldsPartialSegment(['string'])], 1)
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
