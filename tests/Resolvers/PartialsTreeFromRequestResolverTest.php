<?php

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\TreeNodes\AllTreeNode;
use Spatie\LaravelData\Support\TreeNodes\DisabledTreeNode;
use Spatie\LaravelData\Support\TreeNodes\ExcludedTreeNode;
use Spatie\LaravelData\Support\TreeNodes\PartialTreeNode;
use Spatie\LaravelData\Support\TreeNodes\TreeNode;
use Spatie\LaravelData\Tests\Fakes\CircData;
use Spatie\LaravelData\Tests\Fakes\LazyData;
use Spatie\LaravelData\Tests\Fakes\MultiLazyData;
use Spatie\LaravelData\Tests\Fakes\SimpleChildDataWithMappedOutputName;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedOutputName;
use Spatie\LaravelData\Tests\Fakes\UlarData;

// Todo: replace
//it('will correctly reduce a tree based upon allowed includes', function (
//    ?array $lazyDataAllowedIncludes,
//    ?array $dataAllowedIncludes,
//    ?string $requestedAllowedIncludes,
//    TreeNode $expectedIncludes
//) {
//    LazyData::setAllowedIncludes($lazyDataAllowedIncludes);
//
//    $data = new class (
//        'Hello',
//        LazyData::from('Hello'),
//        LazyData::collect(['Hello', 'World'])
//    ) extends Data {
//        public static ?array $allowedIncludes;
//
//        public function __construct(
//            public string $property,
//            public LazyData $nested,
//            #[DataCollectionOf(LazyData::class)]
//            public array $collection,
//        ) {
//        }
//
//        public static function allowedRequestIncludes(): ?array
//        {
//            return static::$allowedIncludes;
//        }
//    };
//
//    $data::$allowedIncludes = $dataAllowedIncludes;
//
//    $request = request();
//
//    if ($requestedAllowedIncludes !== null) {
//        $request->merge([
//            'include' => $requestedAllowedIncludes,
//        ]);
//    }
//
//    $trees = $this->resolver->execute($data, $request);
//
//    expect($trees->lazyIncluded)->toEqual($expectedIncludes);
//})->with(function () {
//    yield 'disallowed property inclusion' => [
//        'lazyDataAllowedIncludes' => [],
//        'dataAllowedIncludes' => [],
//        'requestedIncludes' => 'property',
//        'expectedIncludes' => new ExcludedTreeNode(),
//    ];
//
//    yield 'allowed property inclusion' => [
//        'lazyDataAllowedIncludes' => [],
//        'dataAllowedIncludes' => ['property'],
//        'requestedIncludes' => 'property',
//        'expectedIncludes' => new PartialTreeNode([
//            'property' => new ExcludedTreeNode(),
//        ]),
//    ];
//
//    yield 'allowed data property inclusion without nesting' => [
//        'lazyDataAllowedIncludes' => [],
//        'dataAllowedIncludes' => ['nested'],
//        'requestedIncludes' => 'nested.name',
//        'expectedIncludes' => new PartialTreeNode([
//            'nested' => new ExcludedTreeNode(),
//        ]),
//    ];
//
//    yield 'allowed data property inclusion with nesting' => [
//        'lazyDataAllowedIncludes' => ['name'],
//        'dataAllowedIncludes' => ['nested'],
//        'requestedIncludes' => 'nested.name',
//        'expectedIncludes' => new PartialTreeNode([
//            'nested' => new PartialTreeNode([
//                'name' => new ExcludedTreeNode(),
//            ]),
//        ]),
//    ];
//
//    yield 'allowed data collection property inclusion without nesting' => [
//        'lazyDataAllowedIncludes' => [],
//        'dataAllowedIncludes' => ['collection'],
//        'requestedIncludes' => 'collection.name',
//        'expectedIncludes' => new PartialTreeNode([
//            'collection' => new ExcludedTreeNode(),
//        ]),
//    ];
//
//    yield 'allowed data collection property inclusion with nesting' => [
//        'lazyDataAllowedIncludes' => ['name'],
//        'dataAllowedIncludes' => ['collection'],
//        'requestedIncludes' => 'collection.name',
//        'expectedIncludes' => new PartialTreeNode([
//            'collection' => new PartialTreeNode([
//                'name' => new ExcludedTreeNode(),
//            ]),
//        ]),
//    ];
//
//    yield 'allowed nested data property inclusion without defining allowed includes on nested' => [
//        'lazyDataAllowedIncludes' => null,
//        'dataAllowedIncludes' => ['nested'],
//        'requestedIncludes' => 'nested.name',
//        'expectedIncludes' => new PartialTreeNode([
//            'nested' => new PartialTreeNode([
//                'name' => new ExcludedTreeNode(),
//            ]),
//        ]),
//    ];
//
//    yield 'allowed all nested data property inclusion without defining allowed includes on nested' => [
//        'lazyDataAllowedIncludes' => null,
//        'dataAllowedIncludes' => ['nested'],
//        'requestedIncludes' => 'nested.*',
//        'expectedIncludes' => new PartialTreeNode([
//            'nested' => new AllTreeNode(),
//        ]),
//    ];
//
//    yield 'disallowed all nested data property inclusion ' => [
//        'lazyDataAllowedIncludes' => [],
//        'dataAllowedIncludes' => ['nested'],
//        'requestedIncludes' => 'nested.*',
//        'expectedIncludes' => new PartialTreeNode([
//            'nested' => new ExcludedTreeNode(),
//        ]),
//    ];
//
//    yield 'multi property inclusion' => [
//        'lazyDataAllowedIncludes' => null,
//        'dataAllowedIncludes' => ['nested', 'property'],
//        'requestedIncludes' => 'nested.*,property',
//        'expectedIncludes' => new PartialTreeNode([
//            'property' => new ExcludedTreeNode(),
//            'nested' => new AllTreeNode(),
//        ]),
//    ];
//
//    yield 'without property inclusion' => [
//        'lazyDataAllowedIncludes' => null,
//        'dataAllowedIncludes' => ['nested', 'property'],
//        'requestedIncludes' => null,
//        'expectedIncludes' => new DisabledTreeNode(),
//    ];
//});

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

it('handles parsing includes from request', function (array $input, array $expected) {
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
