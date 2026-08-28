<?php

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Resolvers\DataMorphClassResolver;
use Spatie\LaravelData\Support\Creation\Actions\FillAction;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\Creation\CreationContextFactory;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Tests\Fakes\DataWithMapper;
use Spatie\LaravelData\Tests\Fakes\FillTestInjectable;
use Spatie\LaravelData\Tests\Fakes\Models\FakeModel;
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
