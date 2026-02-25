<?php

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapDotExpandedName;
use Spatie\LaravelData\Attributes\MapDotExpandedOutputName;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithExpandedDottedProperty;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedDottedProperty;

it('can map dotted property names when transforming without expand', function () {
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

it('can map dotted property names when transforming with expand', function () {
    $data = new SimpleDataWithExpandedDottedProperty('hello');
    $dataCollection = SimpleDataWithExpandedDottedProperty::collect([
        ['dotted.description' => 'never'],
        ['dotted.description' => 'gonna'],
        ['dotted.description' => 'give'],
        ['dotted.description' => 'you'],
        ['dotted' => ['description' => 'up']],
    ]);

    $dataClass = new class ('hello', $data, $data, $dataCollection, $dataCollection) extends Data {
        public function __construct(
            #[MapDotExpandedOutputName('dotted.property')]
            public string $string,
            public SimpleDataWithExpandedDottedProperty $nested,
            #[MapDotExpandedOutputName('dotted.nested_other')]
            public SimpleDataWithExpandedDottedProperty $nested_renamed,
            #[DataCollectionOf(SimpleDataWithExpandedDottedProperty::class)]
            public array $nested_collection,
            #[
                MapDotExpandedOutputName('dotted.nested_other_collection'),
                DataCollectionOf(SimpleDataWithExpandedDottedProperty::class)
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

it('can use all four attribute combinations for dot notation expansion', function () {
    // Test all four combinations for OUTPUT transformation
    $dataClass = new class ('value1', 'value2', 'value3', 'value4') extends Data {
        public function __construct(
            #[MapDotExpandedOutputName('user.profile.name')]
            public string $property1,
            #[MapDotExpandedName('user.profile.email')]
            public string $property2,
            #[MapOutputName('user.settings.theme', expandDotNotation: true)]
            public string $property3,
            #[MapName('user.settings.language', expandDotNotation: true)]
            public string $property4,
        ) {
        }
    };

    // Test output transformation - all four should expand dot notation
    expect($dataClass->toArray())->toMatchArray([
        'user' => [
            'profile' => [
                'name' => 'value1',
                'email' => 'value2',
            ],
            'settings' => [
                'theme' => 'value3',
                'language' => 'value4',
            ],
        ],
    ]);

    // Test INPUT mapping with MapDotExpandedName and MapName (both handle input)
    $inputTestClass = new class ('default1', 'default2') extends Data {
        public function __construct(
            #[MapDotExpandedName('user.profile.email')]
            public string $email,
            #[MapName('user.settings.language', expandDotNotation: true)]
            public string $language,
        ) {
        }
    };

    $fromArray = $inputTestClass::from([
        'user' => [
            'profile' => [
                'email' => 'test@example.com',
            ],
            'settings' => [
                'language' => 'fr',
            ],
        ],
    ]);

    expect($fromArray->email)->toBe('test@example.com');
    expect($fromArray->language)->toBe('fr');
});
