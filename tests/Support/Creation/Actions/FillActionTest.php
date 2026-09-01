<?php

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Exceptions\CannotCreateAbstractClass;
use Spatie\LaravelData\Normalizers\Normalized\NormalizedModel;
use Spatie\LaravelData\Support\Creation\Actions\FillAction;
use Spatie\LaravelData\Support\Creation\Actions\NormalizePayloadAction;
use Spatie\LaravelData\Support\Creation\Actions\ReadDataPropertyAction;
use Spatie\LaravelData\Support\Creation\Actions\ResolveMorphedDataClassAction;
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
use Spatie\LaravelData\Tests\Fakes\NestedData;
use Spatie\LaravelData\Tests\Fakes\PropertyMorphableDataA;
use Spatie\LaravelData\Tests\Fakes\PropertyMorphableDataB;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedProperty;

function fillAction(): FillAction
{
    return new FillAction(
        app(DataConfig::class),
        new NormalizePayloadAction(
            array_map(fn (string $normalizer) => app($normalizer), config('data.normalizers')),
        ),
        new ReadDataPropertyAction(),
        new ResolveMorphedDataClassAction(app(DataConfig::class), new ReadDataPropertyAction()),
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

it('leaves every property absent when a root payload cannot be normalized', function () {
    $state = fillAction()->execute(fillContext(SimpleData::class), [42]);

    expect($state->payload())->toBe([]);
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
        ->prepareDataHook(fn (array $payloads, string $class, string $path) => [
            ['string' => strtoupper($payloads[0]['string'])],
        ])
        ->get();

    $state = fillAction()->execute($context, [['string' => 'hello']]);

    expect($state->payload())->toBe(['string' => 'HELLO']);
});

it('chains prepareData hooks in registration order', function () {
    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->prepareDataHook(fn (array $payloads, string $class, string $path) => [
            ['string' => $payloads[0]['string'].'-one'],
        ])
        ->prepareDataHook(fn (array $payloads, string $class, string $path) => [
            ['string' => $payloads[0]['string'].'-two'],
        ])
        ->get();

    $state = fillAction()->execute($context, [['string' => 'start']]);

    expect($state->payload())->toBe(['string' => 'start-one-two']);
});

it('hands every payload to a prepareData hook so it can merge them', function () {
    $dataClass = new class ('', '') extends Data {
        public function __construct(
            public string $first,
            public string $second,
        ) {
        }
    };

    $context = CreationContextFactory::createFromConfig($dataClass::class)
        ->prepareDataHook(fn (array $payloads, string $class, string $path) => [array_merge(...$payloads)])
        ->get();

    $state = fillAction()->execute($context, [
        ['first' => 'from first'],
        ['first' => 'ignored', 'second' => 'from second'],
    ]);

    expect($state->payload())->toBe([
        'first' => 'ignored',
        'second' => 'from second',
    ]);
});

it('fires prepareData hooks once for a single payload list', function () {
    $calls = 0;

    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->prepareDataHook(function (array $payloads, string $class, string $path) use (&$calls) {
            $calls++;

            return $payloads;
        })
        ->get();

    fillAction()->execute($context, [['string' => 'one'], ['string' => 'two']]);

    expect($calls)->toBe(1);
});

it('reindexes the payload list a prepareData hook returns', function () {
    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->prepareDataHook(fn (array $payloads, string $class, string $path) => array_filter(
            $payloads,
            fn (array $payload) => array_key_exists('string', $payload)
        ))
        ->get();

    $state = fillAction()->execute($context, [[], ['string' => 'second']]);

    expect($state->payload())->toBe(['string' => 'second']);
});

it('leaves every property absent when a prepareData hook returns no payloads', function () {
    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->prepareDataHook(fn (array $payloads, string $class, string $path) => [])
        ->get();

    $state = fillAction()->execute($context, [['string' => 'hello']]);

    expect($state->payload())->toBe([]);
});

it('fires prepareData hooks with an empty list when nothing was given', function () {
    $seen = null;

    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->prepareDataHook(function (array $normalized, string $class, string $path) use (&$seen) {
            $seen = $normalized;

            return $normalized;
        })
        ->get();

    fillAction()->execute($context, []);

    expect($seen)->toBe([]);
});

it('lets a prepareData hook supply the payload when nothing was given', function () {
    $context = CreationContextFactory::createFromConfig(SimpleData::class)
        ->prepareDataHook(fn (array $normalized, string $class, string $path) => [['string' => 'from hook']])
        ->get();

    $state = fillAction()->execute($context, []);

    expect($state->payload())->toBe(['string' => 'from hook']);
});

it('still injects values when no payload was given', function () {
    $dataClass = new class ('') extends Data {
        public function __construct(
            #[FillTestInjectable(value: 'injected')]
            public string $string,
        ) {
        }
    };

    $state = fillAction()->execute(fillContext($dataClass::class), []);

    expect($state->payload())->toBe(['string' => 'injected']);
});

it('hands normalized sources to prepareData hooks untouched', function () {
    $model = new FakeModel();
    $model->setRawAttributes(['string' => 'Hello']);

    $seen = null;

    $context = CreationContextFactory::createFromConfig(FillFakeModelData::class)
        ->prepareDataHook(function (array $payloads, string $class, string $path) use (&$seen) {
            $seen = $payloads;

            return $payloads;
        })
        ->get();

    $state = fillAction()->execute($context, [$model]);

    expect($seen[0])->toBeInstanceOf(NormalizedModel::class)
        ->and($state->payload())->toBe(['string' => 'Hello']);
});

it('lets a prepareData hook replace a normalized source with an array', function () {
    $model = new FakeModel();
    $model->setRawAttributes(['string' => 'Hello']);

    $context = CreationContextFactory::createFromConfig(FillFakeModelData::class)
        ->prepareDataHook(fn (array $payloads, string $class, string $path) => [['string' => 'replaced']])
        ->get();

    $state = fillAction()->execute($context, [$model]);

    expect($state->payload())->toBe(['string' => 'replaced']);
});

it('hands the original payloads to prepareData hooks alongside the sources', function () {
    $model = new FakeModel();
    $model->setRawAttributes(['string' => 'Hello']);

    $seenOriginals = null;

    $context = CreationContextFactory::createFromConfig(FillFakeModelData::class)
        ->prepareDataHook(function (array $payloads, string $class, string $path, array $originals) use (&$seenOriginals) {
            $seenOriginals = $originals;

            return $payloads;
        })
        ->get();

    fillAction()->execute($context, [$model]);

    expect($seenOriginals)->toBe([$model]);
});

it('hands the original payload to prepareData hooks on nested nodes', function () {
    $seen = [];

    $context = CreationContextFactory::createFromConfig(NestedData::class)
        ->prepareDataHook(function (array $payloads, string $class, string $path, array $originals) use (&$seen) {
            $seen[$class] = $originals;

            return $payloads;
        })
        ->get();

    fillAction()->execute($context, [['simple' => ['string' => 'original']]]);

    expect($seen[SimpleData::class])->toBe([['string' => 'original']]);
});

it('runs prepareData hooks before morph resolution so they can repair the discriminator', function () {
    $context = CreationContextFactory::createFromConfig(AbstractPropertyMorphableData::class)
        ->prepareDataHook(fn (array $payloads, string $class, string $path) => [
            ['variant' => 'a', 'a' => $payloads[0]['kind']],
        ])
        ->get();

    $state = fillAction()->execute($context, [['kind' => 'repaired']]);

    expect($state->payload())->toBe(['a' => 'repaired', 'variant' => 'a'])
        ->and($state->structure()['class'])->toBe(PropertyMorphableDataA::class);
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
        ->prepareDataHook(function (array $payloads, string $class, string $path) {
            if ($class === SimpleData::class) {
                return [['string' => 'HOOKED']];
            }

            return $payloads;
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

it('keeps empty collections in the payload', function () {
    $state = fillAction()->execute(fillContext(fillCollectionDataClass()), [
        ['items' => []],
    ]);

    expect($state->payload())->toBe(['items' => []]);
});

it('keeps collection items with no recognized keys in the payload', function () {
    $dataClass = new class () extends Data {
        #[DataCollectionOf(SimpleData::class)]
        public array $items = [];
    };

    $state = fillAction()->execute(fillContext($dataClass::class), [
        ['items' => [['unknownKey' => 'x'], ['string' => 'ok']]],
    ]);

    expect($state->payload())->toBe([
        'items' => [
            [],
            ['string' => 'ok'],
        ],
    ]);
});

it('keeps nested objects with no recognized keys in the payload', function () {
    $state = fillAction()->execute(fillContext(NestedData::class), [
        ['simple' => ['unknownKey' => 'x']],
    ]);

    expect($state->payload())->toBe(['simple' => []]);
});

it('writes explicit null nested values as is', function () {
    $state = fillAction()->execute(fillContext(NestedData::class), [
        ['simple' => null],
    ]);

    expect($state->payload())->toBe(['simple' => null]);
});

it('writes non normalizable nested values as is', function () {
    $state = fillAction()->execute(fillContext(NestedData::class), [
        ['simple' => 123],
    ]);

    expect($state->payload())->toBe(['simple' => 123]);
});

it('enters nested json strings', function () {
    $state = fillAction()->execute(fillContext(NestedData::class), [
        ['simple' => '{"string":"Hello"}'],
    ]);

    expect($state->payload())->toBe(['simple' => ['string' => 'Hello']]);
});

it('writes non normalizable collection items as is', function () {
    $state = fillAction()->execute(fillContext(fillCollectionDataClass()), [
        ['items' => ['nonsense']],
    ]);

    expect($state->payload())->toBe(['items' => ['nonsense']]);
});

it('writes paginators as is', function () {
    $paginator = new \Illuminate\Pagination\LengthAwarePaginator([['string' => 'a']], 1, 15);

    $state = fillAction()->execute(fillContext(fillCollectionDataClass()), [
        ['items' => $paginator],
    ]);

    expect($state->payload()['items'])->toBe($paginator);
});

it('resolves morph classes for nested data object properties', function () {
    $dataClass = new class () extends Data {
        public ?AbstractPropertyMorphableData $morph = null;
    };

    $state = fillAction()->execute(fillContext($dataClass::class), [
        ['morph' => ['variant' => 'a', 'a' => 'x', 'enum' => 'foo']],
    ]);

    expect($state->structure()['children']['morph']['class'])->toBe(PropertyMorphableDataA::class);
});
