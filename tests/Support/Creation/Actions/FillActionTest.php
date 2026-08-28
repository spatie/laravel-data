<?php

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Exceptions\CannotCreateAbstractClass;
use Spatie\LaravelData\Resolvers\DataMorphClassResolver;
use Spatie\LaravelData\Support\Creation\Actions\FillAction;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Tests\Fakes\AbstractPropertyMorphableData;
use Spatie\LaravelData\Tests\Fakes\DataWithMapper;
use Spatie\LaravelData\Tests\Fakes\FillFakeModelData;
use Spatie\LaravelData\Tests\Fakes\FillNestedModelData;
use Spatie\LaravelData\Tests\Fakes\FillTestInjectable;
use Spatie\LaravelData\Tests\Fakes\Models\FakeModel;
use Spatie\LaravelData\Tests\Fakes\Models\FakeNestedModel;
use Spatie\LaravelData\Tests\Fakes\MultiNestedData;
use Spatie\LaravelData\Tests\Fakes\PropertyMorphableDataA;
use Spatie\LaravelData\Tests\Fakes\PropertyMorphableDataB;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedProperty;

function fillAction(): FillAction
{
    return new FillAction(
        app(DataConfig::class),
        app(DataMorphClassResolver::class),
        array_map(fn (string $normalizer) => app($normalizer), config('data.normalizers')),
    );
}

function fillContext(string $dataClass): CreationContext
{
    return CreationContextFactory::createFromConfig($dataClass)->get();
}

it('fills scalar properties from an array payload', function () {
    $state = fillAction()->execute(fillContext(SimpleData::class), [['string' => 'Hello']]);

    expect($state->payload())->toBe(['string' => 'Hello'])
        ->and($state->structure())->toBe([
            'class' => SimpleData::class,
            'mappings' => [],
            'children' => [],
        ]);
});

it('fills nothing from an empty payload list', function () {
    $state = fillAction()->execute(fillContext(SimpleData::class), []);

    expect($state->payload())->toBe([])
        ->and($state->structure()['class'])->toBe(SimpleData::class);
});

it('reads mapped keys first and records the mapping', function () {
    $state = fillAction()->execute(fillContext(SimpleDataWithMappedProperty::class), [
        ['description' => 'Hello', 'string' => 'ignored'],
    ]);

    expect($state->payload())->toBe(['description' => 'Hello'])
        ->and($state->structure()['mappings'])->toBe(['string' => 'description']);
});

it('falls back to the property name when the mapped key is absent', function () {
    $state = fillAction()->execute(fillContext(SimpleDataWithMappedProperty::class), [
        ['string' => 'Hello'],
    ]);

    expect($state->payload())->toBe(['string' => 'Hello'])
        ->and($state->structure()['mappings'])->toBe([]);
});

it('records the mapped key canonically when the value is absent', function () {
    $state = fillAction()->execute(fillContext(SimpleDataWithMappedProperty::class), [[]]);

    expect($state->payload())->toBe([])
        ->and($state->structure()['mappings'])->toBe(['string' => 'description']);
});

it('ignores mapped keys when property name mapping is off', function () {
    $context = CreationContextFactory::createFromConfig(SimpleDataWithMappedProperty::class)
        ->withoutPropertyNameMapping()
        ->get();

    $state = fillAction()->execute($context, [
        ['description' => 'mapped', 'string' => 'plain'],
    ]);

    expect($state->payload())->toBe(['string' => 'plain'])
        ->and($state->structure()['mappings'])->toBe([]);
});

it('applies class level mappers to every property', function () {
    $state = fillAction()->execute(fillContext(DataWithMapper::class), [
        ['cased_property' => 'Hello'],
    ]);

    expect($state->payload())->toBe(['cased_property' => 'Hello'])
        ->and($state->structure()['mappings'])->toBe([
            'casedProperty' => 'cased_property',
            'dataCasedProperty' => 'data_cased_property',
            'dataCollectionCasedProperty' => 'data_collection_cased_property',
        ]);
});

it('lets the first source containing a key win', function () {
    $state = fillAction()->execute(fillContext(SimpleData::class), [
        ['string' => 'first'],
        ['string' => 'second'],
    ]);

    expect($state->payload())->toBe(['string' => 'first']);
});

it('reads from a model source', function () {
    $model = new FakeModel();
    $model->setRawAttributes(['string' => 'Hello']);

    $state = fillAction()->execute(fillContext(SimpleData::class), [$model]);

    expect($state->payload())->toBe(['string' => 'Hello']);
});

it('applies prepareData hooks before filling', function () {
    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->prepareData(fn (mixed $payload, string $class, string $path) => ['string' => strtoupper($payload['string'])])
        ->get();

    $state = fillAction()->execute($context, [['string' => 'hello']]);

    expect($state->payload())->toBe(['string' => 'HELLO']);
});

it('chains prepareData hooks in registration order', function () {
    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->prepareData(fn (mixed $payload, string $class, string $path) => ['string' => $payload['string'].'-one'])
        ->prepareData(fn (mixed $payload, string $class, string $path) => ['string' => $payload['string'].'-two'])
        ->get();

    $state = fillAction()->execute($context, [['string' => 'start']]);

    expect($state->payload())->toBe(['string' => 'start-one-two']);
});

it('injects a value when the payload does not provide one', function () {
    $dataClass = new class ('') extends Data {
        public function __construct(
            #[FillTestInjectable(value: 'injected')]
            public string $string,
        ) {
        }
    };

    $state = fillAction()->execute(fillContext($dataClass::class), [[]]);

    expect($state->payload())->toBe(['string' => 'injected']);
});

it('replaces a present value when the attribute wants that', function () {
    $dataClass = new class ('') extends Data {
        public function __construct(
            #[FillTestInjectable(value: 'injected', replace: true)]
            public string $string,
        ) {
        }
    };

    $state = fillAction()->execute(fillContext($dataClass::class), [['string' => 'original']]);

    expect($state->payload())->toBe(['string' => 'injected']);
});

it('keeps a present value when the attribute does not replace', function () {
    $dataClass = new class ('') extends Data {
        public function __construct(
            #[FillTestInjectable(value: 'injected', replace: false)]
            public string $string,
        ) {
        }
    };

    $state = fillAction()->execute(fillContext($dataClass::class), [['string' => 'original']]);

    expect($state->payload())->toBe(['string' => 'original']);
});

it('falls through Skipped to the next injection attribute', function () {
    $dataClass = new class ('') extends Data {
        public function __construct(
            #[FillTestInjectable(skip: true), FillTestInjectable(value: 'second')]
            public string $string,
        ) {
        }
    };

    $state = fillAction()->execute(fillContext($dataClass::class), [[]]);

    expect($state->payload())->toBe(['string' => 'second']);
});

it('leaves the value absent when every injection attribute skips', function () {
    $dataClass = new class ('') extends Data {
        public function __construct(
            #[FillTestInjectable(skip: true)]
            public string $string,
        ) {
        }
    };

    $state = fillAction()->execute(fillContext($dataClass::class), [[]]);

    expect($state->payload())->toBe([]);
});

it('fills nested data objects recursively', function () {
    $state = fillAction()->execute(fillContext(NestedData::class), [
        ['simple' => ['string' => 'Hello']],
    ]);

    expect($state->payload())->toBe(['simple' => ['string' => 'Hello']])
        ->and($state->structure()['children'])->toBe([
            'simple' => [
                'class' => SimpleData::class,
                'mappings' => [],
                'children' => [],
            ],
        ]);
});

it('passes finished nested data instances through untouched', function () {
    $simple = new SimpleData('Hello');

    $state = fillAction()->execute(fillContext(NestedData::class), [
        ['simple' => $simple],
    ]);

    expect($state->payload())->toBe(['simple' => $simple])
        ->and($state->structure()['children'])->toBe([]);
});

it('fills nested data under mapped keys', function () {
    $state = fillAction()->execute(fillContext(DataWithMapper::class), [
        ['cased_property' => 'Hello', 'data_cased_property' => ['string' => 'Nested']],
    ]);

    expect($state->payload())->toBe([
        'cased_property' => 'Hello',
        'data_cased_property' => ['string' => 'Nested'],
    ])
        ->and($state->structure()['children']['dataCasedProperty']['class'])->toBe(SimpleData::class);
});

it('fires prepareData hooks per nested node', function () {
    $context = CreationContextFactory::createFromConfig(NestedData::class)
        ->prepareData(function (mixed $payload, string $class, string $path) {
            if ($class === SimpleData::class) {
                return ['string' => 'HOOKED'];
            }

            return $payload;
        })
        ->get();

    $state = fillAction()->execute($context, [['simple' => ['string' => 'original']]]);

    expect($state->payload())->toBe(['simple' => ['string' => 'HOOKED']]);
});

it('fills nested data from a model relation lazily', function () {
    $related = new FakeModel();
    $related->setRawAttributes(['string' => 'Hello']);

    $model = new FakeNestedModel();
    $model->setRelation('fakeModel', $related);

    $state = fillAction()->execute(fillContext(FillNestedModelData::class), [$model]);

    expect($state->payload())->toBe(['fakeModel' => ['string' => 'Hello']])
        ->and($state->structure()['children']['fakeModel']['class'])->toBe(FillFakeModelData::class);
});

function fillCollectionDataClass(): string
{
    $dataClass = new class () extends Data {
        #[DataCollectionOf(SimpleData::class)]
        public array $items = [];
    };

    return $dataClass::class;
}

it('fills data collections with per index recursion', function () {
    $state = fillAction()->execute(fillContext(MultiNestedData::class), [[
        'nested' => ['simple' => ['string' => 'a']],
        'nestedCollection' => [
            ['simple' => ['string' => 'b']],
            ['simple' => ['string' => 'c']],
        ],
    ]]);

    expect($state->payload())->toBe([
        'nested' => ['simple' => ['string' => 'a']],
        'nestedCollection' => [
            ['simple' => ['string' => 'b']],
            ['simple' => ['string' => 'c']],
        ],
    ])
        ->and($state->structure()['children']['nestedCollection'])->toBe([
            'class' => NestedData::class,
            'mappings' => [],
            'children' => [
                'simple' => [
                    'class' => SimpleData::class,
                    'mappings' => [],
                    'children' => [],
                ],
            ],
        ]);
});

it('passes finished collection items through untouched', function () {
    $finished = new SimpleData('a');

    $state = fillAction()->execute(fillContext(fillCollectionDataClass()), [
        ['items' => [$finished, ['string' => 'b']]],
    ]);

    expect($state->payload())->toBe(['items' => [$finished, ['string' => 'b']]]);
});

it('iterates Laravel collections as collection input', function () {
    $state = fillAction()->execute(fillContext(fillCollectionDataClass()), [
        ['items' => collect([['string' => 'a']])],
    ]);

    expect($state->payload())->toBe(['items' => [['string' => 'a']]])
        ->and($state->structure()['children']['items']['class'])->toBe(SimpleData::class);
});

it('writes non iterable collection values as is', function () {
    $state = fillAction()->execute(fillContext(fillCollectionDataClass()), [
        ['items' => 'nonsense'],
    ]);

    expect($state->payload())->toBe(['items' => 'nonsense']);
});

it('resolves the morph class at the root', function () {
    $state = fillAction()->execute(fillContext(AbstractPropertyMorphableData::class), [
        ['variant' => 'a', 'a' => 'foo', 'enum' => 'foo'],
    ]);

    expect($state->structure()['class'])->toBe(PropertyMorphableDataA::class)
        ->and($state->payload())->toBe(['a' => 'foo', 'enum' => 'foo', 'variant' => 'a']);
});

it('throws when the morph class cannot be resolved', function () {
    fillAction()->execute(fillContext(AbstractPropertyMorphableData::class), [[]]);
})->throws(CannotCreateAbstractClass::class);

it('records divergent morph classes per collection item', function () {
    $dataClass = new class () extends Data {
        #[DataCollectionOf(AbstractPropertyMorphableData::class)]
        public array $items = [];
    };

    $state = fillAction()->execute(fillContext($dataClass::class), [[
        'items' => [
            ['variant' => 'a', 'a' => 'foo', 'enum' => 'foo'],
            ['variant' => 'b', 'b' => 'bar'],
        ],
    ]]);

    expect($state->structure()['children']['items']['class'])->toBe(AbstractPropertyMorphableData::class)
        ->and($state->structure()['children']['items']['indexClasses'])->toBe([
            0 => PropertyMorphableDataA::class,
            1 => PropertyMorphableDataB::class,
        ])
        ->and($state->payload())->toBe([
            'items' => [
                ['a' => 'foo', 'enum' => 'foo', 'variant' => 'a'],
                ['b' => 'bar', 'variant' => 'b'],
            ],
        ]);
});
