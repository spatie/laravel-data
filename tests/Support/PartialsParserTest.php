<?php

namespace Spatie\LaravelData\Tests\Support;

use Generator;
use Spatie\LaravelData\Support\PartialsParser;
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
    public function it_can_parse_directives(array $partials, array $expected)
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
        yield "root property" => [
            'partials' => [
                'name',
            ],
            'expected' => [
                'name' => [],
            ],
        ];

        yield "root multi-property" => [
            'partials' => [
                '{name, age}',
            ],
            'expected' => [
                'name' => [],
                'age' => [],
            ],
        ];

        yield "root star" => [
            'partials' => [
                '*',
            ],
            'expected' => [
                '*',
            ],
        ];

        yield "root star overrules" => [
            'partials' => [
                'name',
                '*',
                'age',
            ],
            'expected' => [
                '*',
            ],
        ];

        yield "root combination" => [
            'partials' => [
                'name',
                '{name, age}',
                'age',
                'gender',
            ],
            'expected' => [
                'name' => [],
                'age' => [],
                'gender' => [],
            ],
        ];
    }

    public function nestedPartialsProvider(): Generator
    {
        yield "nested property" => [
            'partials' => [
                'struct.name',
            ],
            'expected' => [
                'struct' => [
                    'name' => [],
                ],
            ],
        ];

        yield "nested multi-property" => [
            'partials' => [
                'struct.{name, age}',
            ],
            'expected' => [
                'struct' => [
                    'name' => [],
                    'age' => [],
                ],
            ],
        ];

        yield "nested star" => [
            'partials' => [
                'struct.*',
            ],
            'expected' => [
                'struct' => ['*'],
            ],
        ];

        yield "nested star overrules" => [
            'partials' => [
                'struct.name',
                'struct.*',
                'struct.age',
            ],
            'expected' => [
                'struct' => ['*'],
            ],
        ];

        yield "nested combination" => [
            'partials' => [
                'struct.name',
                'struct.{name, age}',
                'struct.age',
                'struct.gender',
            ],
            'expected' => [
                'struct' => [
                    'name' => [],
                    'age' => [],
                    'gender' => [],
                ],
            ],
        ];
    }

    public function invalidPartialsProvider(): Generator
    {
        yield "nested property on all" => [
            'partials' => [
                '*.name',
            ],
            'expected' => [
                '*',
            ],
        ];

        yield "nested property on multi-property" => [
            'partials' => [
                '{name, age}.name',
            ],
            'expected' => [
                'name' => [],
                'age' => [],
            ],
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
            'expected' => [
                'name' => [],
                'age' => [],
                'posts' => [
                    'name' => [],
                    'tags' => ['*'],
                ],
                'identities' => [
                    'auth0' => [
                        'name' => [],
                        'email' => [],
                    ],
                ],
                'books' => ['*'],
            ],
        ];
    }
}
