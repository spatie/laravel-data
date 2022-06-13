
// Da
uses(TestCase::class);
tasets
dataset('onlyInclusion', function () {
    yield 'single' => [
        'directive' => ['first'],
        'expectedOnly' => [
            'first' => 'A',
        ],
        'expectedExcept' => [
            'second' => 'B',
            'nested' => ['first' => 'C', 'second' => 'D'],
            'collection' => [
                ['first' => 'E', 'second' => 'F'],
                ['first' => 'G', 'second' => 'H'],
            ],
        ],
    ];

    yield 'multi' => [
        'directive' => ['first', 'second'],
        'expectedOnly' => [
            'first' => 'A',
            'second' => 'B',
        ],
        'expectedExcept' => [
            'nested' => ['first' => 'C', 'second' => 'D'],
            'collection' => [
                ['first' => 'E', 'second' => 'F'],
                ['first' => 'G', 'second' => 'H'],
            ],
        ],
    ];

    yield 'multi-2' => [
        'directive' => ['{first,second}'],
        'expectedOnly' => [
            'first' => 'A',
            'second' => 'B',
        ],
        'expectedExcept' => [
            'nested' => ['first' => 'C', 'second' => 'D'],
            'collection' => [
                ['first' => 'E', 'second' => 'F'],
                ['first' => 'G', 'second' => 'H'],
            ],
        ],
    ];

    yield 'all' => [
        'directive' => ['*'],
        'expectedOnly' => [
            'first' => 'A',
            'second' => 'B',
            'nested' => ['first' => 'C', 'second' => 'D'],
            'collection' => [
                ['first' => 'E', 'second' => 'F'],
                ['first' => 'G', 'second' => 'H'],
            ],
        ],
        'expectedExcept' => [],
    ];

    yield 'nested' => [
        'directive' => ['nested'],
        'expectedOnly' => [
            'nested' => [],
        ],
        'expectedExcept' => [
            'first' => 'A',
            'second' => 'B',
            'collection' => [
                ['first' => 'E', 'second' => 'F'],
                ['first' => 'G', 'second' => 'H'],
            ],
        ],
    ];

    yield 'nested.single' => [
        'directive' => ['nested.first'],
        'expectedOnly' => [
            'nested' => ['first' => 'C'],
        ],
        'expectedExcept' => [
            'first' => 'A',
            'second' => 'B',
            'nested' => ['second' => 'D'],
            'collection' => [
                ['first' => 'E', 'second' => 'F'],
                ['first' => 'G', 'second' => 'H'],
            ],
        ],
    ];

    yield 'nested.multi' => [
        'directive' => ['nested.{first, second}'],
        'expectedOnly' => [
            'nested' => ['first' => 'C', 'second' => 'D'],
        ],
        'expectedExcept' => [
            'first' => 'A',
            'second' => 'B',
            'nested' => [],
            'collection' => [
                ['first' => 'E', 'second' => 'F'],
                ['first' => 'G', 'second' => 'H'],
            ],
        ],
    ];

    yield 'nested-all' => [
        'directive' => ['nested.*'],
        'expectedOnly' => [
            'nested' => ['first' => 'C', 'second' => 'D'],
        ],
        'expectedExcept' => [
            'first' => 'A',
            'second' => 'B',
            'nested' => [],
            'collection' => [
                ['first' => 'E', 'second' => 'F'],
                ['first' => 'G', 'second' => 'H'],
            ],
        ],
    ];

    yield 'collection' => [
        'directive' => ['collection'],
        'expectedOnly' => [
            'collection' => [
                [],
                [],
//                    ['first' => 'E', 'second' => 'F'],
//                    ['first' => 'G', 'second' => 'H'],
            ],
        ],
        'expectedExcept' => [
            'first' => 'A',
            'second' => 'B',
            'nested' => ['first' => 'C', 'second' => 'D'],
        ],
    ];

    yield 'collection-single' => [
        'directive' => ['collection.first'],
        'expectedOnly' => [
            'collection' => [
                ['first' => 'E'],
                ['first' => 'G'],
            ],
        ],
        'expectedExcept' => [
            'first' => 'A',
            'second' => 'B',
            'nested' => ['first' => 'C', 'second' => 'D'],
            'collection' => [
                ['second' => 'F'],
                ['second' => 'H'],
            ],
        ],
    ];

    yield 'collection-multi' => [
        'directive' => ['collection.first', 'collection.second'],
        'expectedOnly' => [
            'collection' => [
                ['first' => 'E', 'second' => 'F'],
                ['first' => 'G', 'second' => 'H'],
            ],
        ],
        'expectedExcept' => [
            'first' => 'A',
            'second' => 'B',
            'nested' => ['first' => 'C', 'second' => 'D'],
            'collection' => [
                [],
                [],
            ],
        ],
    ];

    yield 'collection-all' => [
        'directive' => ['collection.*'],
        'expectedOnly' => [
            'collection' => [
                ['first' => 'E', 'second' => 'F'],
                ['first' => 'G', 'second' => 'H'],
            ],
        ],
        'expectedExcept' => [
            'first' => 'A',
            'second' => 'B',
            'nested' => ['first' => 'C', 'second' => 'D'],
            'collection' => [
                [],
                [],
            ],
        ],
    ];
});
