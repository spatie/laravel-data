<?php

namespace Spatie\LaravelData\Tests\Support;

use Generator;
use Spatie\LaravelData\Support\PartialsParser;
use Spatie\LaravelData\Support\TreeNodes\AllTreeNode;
use Spatie\LaravelData\Support\TreeNodes\DisabledTreeNode;
use Spatie\LaravelData\Support\TreeNodes\ExcludedTreeNode;
use Spatie\LaravelData\Support\TreeNodes\PartialTreeNode;
use Spatie\LaravelData\Support\TreeNodes\TreeNode;
use Spatie\LaravelData\Tests\TestCase;

class PartialsParserTest extends TestCase
{
    /**
     * @test
     * @dataProvider directivesProvider
     *
     * @param array $partials
     * @param array $expected
     */
    public function it_can_parse_directives(array $partials, TreeNode $expected)
    {
        $this->assertEquals(
            $expected,
            (new PartialsParser())->execute($partials),
        );
    }

    public function directivesProvider(): Generator
    {
        yield from $this->rootPartialsProvider();
        yield from $this->nestedPartialsProvider();
        yield from $this->invalidPartialsProvider();
        yield from $this->complexPartialsProvider();
    }

    public function rootPartialsProvider(): Generator
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

    public function nestedPartialsProvider(): Generator
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

    public function invalidPartialsProvider(): Generator
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

    public function complexPartialsProvider(): Generator
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
}
