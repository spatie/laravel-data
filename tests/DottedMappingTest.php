<?php

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedDottedProperty;

it('can map dotted property names when transforming without undot', function () {
    config()->set('data.features.expand_dot_notation', false);
    $data = new SimpleDataWithMappedDottedProperty('hello');
    $dataCollection = SimpleDataWithMappedDottedProperty::collect([
        ['dotted.description' => 'never'],
        ['dotted.description' => 'gonna'],
        ['dotted.description' => 'give'],
        ['dotted.description' => 'you'],
        ['dotted' => ['description' => 'up']],
    ]);

    $dataClass = new class ('hello', $data, $data, $dataCollection, $dataCollection) extends Data {
        public function __construct(
            #[MapOutputName('dotted.property')]
            public string $string,
            public SimpleDataWithMappedDottedProperty $nested,
            #[MapOutputName('dotted.nested_other')]
            public SimpleDataWithMappedDottedProperty $nested_renamed,
            #[DataCollectionOf(SimpleDataWithMappedDottedProperty::class)]
            public array $nested_collection,
            #[
                MapOutputName('dotted.nested_other_collection'),
                DataCollectionOf(SimpleDataWithMappedDottedProperty::class)
            ]
            public array $nested_renamed_collection,
        ) {
        }
    };

    expect($dataClass->toArray())->toMatchArray([
        'dotted.property' => 'hello',
        'nested' => [
            'dotted.description' => 'hello',
        ],
        'dotted.nested_other' => [
            'dotted.description' => 'hello',
        ],
        'nested_collection' => [
            ['dotted.description' => 'never'],
            ['dotted.description' => 'gonna'],
            ['dotted.description' => 'give'],
            ['dotted.description' => 'you'],
            ['dotted.description' => 'up'],
        ],
        'dotted.nested_other_collection' => [
            ['dotted.description' => 'never'],
            ['dotted.description' => 'gonna'],
            ['dotted.description' => 'give'],
            ['dotted.description' => 'you'],
            ['dotted.description' => 'up'],
        ],
    ]);
});

it('can map dotted property names when transforming with undot', function () {
    config()->set('data.features.expand_dot_notation', true);
    $data = new SimpleDataWithMappedDottedProperty('hello');
    $dataCollection = SimpleDataWithMappedDottedProperty::collect([
        ['dotted.description' => 'never'],
        ['dotted.description' => 'gonna'],
        ['dotted.description' => 'give'],
        ['dotted.description' => 'you'],
        ['dotted' => ['description' => 'up']],
    ]);

    $dataClass = new class ('hello', $data, $data, $dataCollection, $dataCollection) extends Data {
        public function __construct(
            #[MapOutputName('dotted.property')]
            public string $string,
            public SimpleDataWithMappedDottedProperty $nested,
            #[MapOutputName('dotted.nested_other')]
            public SimpleDataWithMappedDottedProperty $nested_renamed,
            #[DataCollectionOf(SimpleDataWithMappedDottedProperty::class)]
            public array $nested_collection,
            #[
                MapOutputName('dotted.nested_other_collection'),
                DataCollectionOf(SimpleDataWithMappedDottedProperty::class)
            ]
            public array $nested_renamed_collection,
        ) {
        }
    };

    expect($dataClass->toArray())->toMatchArray([
        'dotted' => [
            'property' => 'hello',
            'nested_other' => ['dotted' => ['description' => 'hello']],
            'nested_other_collection' => [
                ['dotted' => ['description' => 'never']],
                ['dotted' => ['description' => 'gonna']],
                ['dotted' => ['description' => 'give']],
                ['dotted' => ['description' => 'you']],
                ['dotted' => ['description' => 'up']],
            ],
        ],
        'nested' => [
            'dotted' => ['description' => 'hello'],
        ],
        'nested_collection' => [
            ['dotted' => ['description' => 'never']],
            ['dotted' => ['description' => 'gonna']],
            ['dotted' => ['description' => 'give']],
            ['dotted' => ['description' => 'you']],
            ['dotted' => ['description' => 'up']],
        ],
    ]);
});
