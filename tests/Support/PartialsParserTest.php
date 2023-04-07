<?php

use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\PartialsParser;
use Spatie\LaravelData\Support\TreeNodes\AllTreeNode;
use Spatie\LaravelData\Support\TreeNodes\DisabledTreeNode;
use Spatie\LaravelData\Support\TreeNodes\ExcludedTreeNode;
use Spatie\LaravelData\Support\TreeNodes\PartialTreeNode;
use Spatie\LaravelData\Support\TreeNodes\TreeNode;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedOutputName;

it('can parse directives', function (array $partials, TreeNode $expected) {
    expect(app(PartialsParser::class))->execute($partials)->toEqual($expected);
})->with(function () {
    yield from rootPartialsProvider();
    yield from nestedPartialsProvider();
    yield from invalidPartialsProvider();
    yield from complexPartialsProvider();
});


function rootPartialsProvider(): Generator
{
    yield "empty" => [
        'partials' => [],
        'expected' => new DisabledTreeNode(),
    ];

    yield "root property" => [
        'partials' => [
            'name',
        ],
        'expected' => new PartialTreeNode([
            'name' => new ExcludedTreeNode(),
        ]),
    ];

    yield "root multi-property" => [
        'partials' => [
            '{name, age}',
        ],
        'expected' => new PartialTreeNode([
            'name' => new ExcludedTreeNode(),
            'age' => new ExcludedTreeNode(),
        ]),
    ];

    yield "root star" => [
        'partials' => [
            '*',
        ],
        'expected' => new AllTreeNode(),
    ];

    yield "root star overrules" => [
        'partials' => [
            'name',
            '*',
            'age',
        ],
        'expected' => new AllTreeNode(),
    ];

    yield "root combination" => [
        'partials' => [
            'name',
            '{name, age}',
            'age',
            'gender',
        ],
        'expected' => new PartialTreeNode([
            'name' => new ExcludedTreeNode(),
            'age' => new ExcludedTreeNode(),
            'gender' => new ExcludedTreeNode(),
        ]),
    ];
}

function nestedPartialsProvider(): Generator
{
    yield "nested property" => [
        'partials' => [
            'struct.name',
        ],
        'expected' => new PartialTreeNode([
            'struct' => new PartialTreeNode([
                'name' => new ExcludedTreeNode(),
            ]),
        ]),
    ];

    yield "nested multi-property" => [
        'partials' => [
            'struct.{name, age}',
        ],
        'expected' => new PartialTreeNode([
            'struct' => new PartialTreeNode([
                'name' => new ExcludedTreeNode(),
                'age' => new ExcludedTreeNode(),
            ]),
        ]),
    ];

    yield "nested star" => [
        'partials' => [
            'struct.*',
        ],
        'expected' => new PartialTreeNode([
            'struct' => new AllTreeNode(),
        ]),
    ];

    yield "nested star overrules" => [
        'partials' => [
            'struct.name',
            'struct.*',
            'struct.age',
        ],
        'expected' => new PartialTreeNode([
            'struct' => new AllTreeNode(),
        ]),
    ];

    yield "nested combination" => [
        'partials' => [
            'struct.name',
            'struct.{name, age}',
            'struct.age',
            'struct.gender',
        ],
        'expected' => new PartialTreeNode([
            'struct' => new PartialTreeNode([
                'name' => new ExcludedTreeNode(),
                'age' => new ExcludedTreeNode(),
                'gender' => new ExcludedTreeNode(),
            ]),
        ]),
    ];
}

function invalidPartialsProvider(): Generator
{
    yield "nested property on all" => [
        'partials' => [
            '*.name',
        ],
        'expected' => new AllTreeNode(),
    ];

    yield "nested property on multi-property" => [
        'partials' => [
            '{name, age}.name',
        ],
        'expected' => new PartialTreeNode([
            'name' => new ExcludedTreeNode(),
            'age' => new ExcludedTreeNode(),
        ]),
    ];
}

function complexPartialsProvider(): Generator
{
    yield "a complex example" => [
        'partials' => [
            'name',
            'age',
            'posts.name',
            'posts.tags.*',
            'identities.auth0.{name,email}',
            'books.title',
            'books.*',
        ],
        'expected' => new PartialTreeNode([
            'name' => new ExcludedTreeNode(),
            'age' => new ExcludedTreeNode(),
            'posts' => new PartialTreeNode([
                'name' => new ExcludedTreeNode(),
                'tags' => new AllTreeNode(),
            ]),
            'identities' => new PartialTreeNode([
                'auth0' => new PartialTreeNode([
                    'name' => new ExcludedTreeNode(),
                    'email' => new ExcludedTreeNode(),
                ]),
            ]),
            'books' => new AllTreeNode(),
        ]),
    ];
}

it('can parse directives with mapping', function (array $partials, TreeNode $expected) {
    $fakeClass = new class () extends Data {
        #[MapOutputName('naam')]
        public string $name;

        #[MapOutputName('leeftijd')]
        public string $age;

        #[MapOutputName('geslacht')]
        public string $gender;

        #[MapOutputName('structuur')]
        public SimpleDataWithMappedOutputName $struct;
    };

    $mapping = app(DataConfig::class)->getDataClass($fakeClass::class)->outputNameMapping->resolve();

    expect(app(PartialsParser::class))
        ->execute($partials, $mapping)
        ->toEqual($expected);
})->with(function () {
    yield "empty" => [
        'partials' => [],
        'expected' => new DisabledTreeNode(),
    ];

    yield "all mapped" => [
        'partials' => [
            'naam',
            '{leeftijd, geslacht}',
            'structuur.any_string',
            'structuur.{paid_amount}',
        ],
        'expected' => new PartialTreeNode([
            'name' => new ExcludedTreeNode(),
            'age' => new ExcludedTreeNode(),
            'gender' => new ExcludedTreeNode(),
            'struct' => new PartialTreeNode([
                'anyString' => new ExcludedTreeNode(),
                'amount' => new ExcludedTreeNode(),
            ]),
        ]),
    ];

    yield "some mapped, some not + non defined mappings" => [
        'partials' => [
            'name',
            'bio',
            '{leeftijd, gender}',
            'structuur.anyString',
            'struct.id',
            'structuur.{paid_amount, child}',
        ],
        'expected' => new PartialTreeNode([
            'name' => new ExcludedTreeNode(),
            'bio' => new ExcludedTreeNode(),
            'age' => new ExcludedTreeNode(),
            'gender' => new ExcludedTreeNode(),
            'struct' => new PartialTreeNode([
                'id' => new ExcludedTreeNode(),
                'anyString' => new ExcludedTreeNode(),
                'amount' => new ExcludedTreeNode(),
                'child' => new ExcludedTreeNode(),
            ]),
        ]),
    ];

    yield "star operator" => [
        'partials' => [
            'structuur.*',
        ],
        'expected' => new PartialTreeNode([
            'struct' => new AllTreeNode(),
        ]),
    ];
});
