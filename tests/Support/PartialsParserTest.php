
// Da
uses(TestCase::class);
tasets
dataset('directivesProvider', function () {
    yield from rootPartialsProvider();
    yield from nestedPartialsProvider();
    yield from invalidPartialsProvider();
    yield from complexPartialsProvider();
});

// Helpers
function rootPartialsProvider(): Generator
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

function nestedPartialsProvider(): Generator
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

function invalidPartialsProvider(): Generator
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
