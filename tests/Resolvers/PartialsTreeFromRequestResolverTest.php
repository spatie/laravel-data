
// Da
uses(TestCase::class);
tasets
dataset('allowedIncludes', function () {
    yield 'disallowed property inclusion' => [
        'lazyDataAllowedIncludes' => [],
        'dataAllowedIncludes' => [],
        'requestedIncludes' => 'property',
        'expectedIncludes' => [],
    ];

    yield 'allowed property inclusion' => [
        'lazyDataAllowedIncludes' => [],
        'dataAllowedIncludes' => ['property'],
        'requestedIncludes' => 'property',
        'expectedIncludes' => [
            'property' => [],
        ],
    ];

    yield 'allowed data property inclusion without nesting' => [
        'lazyDataAllowedIncludes' => [],
        'dataAllowedIncludes' => ['nested'],
        'requestedIncludes' => 'nested.name',
        'expectedIncludes' => [
            'nested' => [],
        ],
    ];

    yield 'allowed data property inclusion with nesting' => [
        'lazyDataAllowedIncludes' => ['name'],
        'dataAllowedIncludes' => ['nested'],
        'requestedIncludes' => 'nested.name',
        'expectedIncludes' => [
            'nested' => [
                'name' => [],
            ],
        ],
    ];

    yield 'allowed data collection property inclusion without nesting' => [
        'lazyDataAllowedIncludes' => [],
        'dataAllowedIncludes' => ['collection'],
        'requestedIncludes' => 'collection.name',
        'expectedIncludes' => [
            'collection' => [],
        ],
    ];

    yield 'allowed data collection property inclusion with nesting' => [
        'lazyDataAllowedIncludes' => ['name'],
        'dataAllowedIncludes' => ['collection'],
        'requestedIncludes' => 'collection.name',
        'expectedIncludes' => [
            'collection' => [
                'name' => [],
            ],
        ],
    ];

    yield 'allowed nested data property inclusion without defining allowed includes on nested' => [
        'lazyDataAllowedIncludes' => null,
        'dataAllowedIncludes' => ['nested'],
        'requestedIncludes' => 'nested.name',
        'expectedIncludes' => [
            'nested' => [
                'name' => [],
            ],
        ],
    ];

    yield 'allowed all nested data property inclusion without defining allowed includes on nested' => [
        'lazyDataAllowedIncludes' => null,
        'dataAllowedIncludes' => ['nested'],
        'requestedIncludes' => 'nested.*',
        'expectedIncludes' => [
            'nested' => ['*'],
        ],
    ];

    yield 'disallowed all nested data property inclusion ' => [
        'lazyDataAllowedIncludes' => ['name'],
        'dataAllowedIncludes' => ['nested'],
        'requestedIncludes' => 'nested.*',
        'expectedIncludes' => [
            'nested' => [],
        ],
    ];

    yield 'multi property inclusion' => [
        'lazyDataAllowedIncludes' => null,
        'dataAllowedIncludes' => ['nested', 'property'],
        'requestedIncludes' => 'nested.*,property',
        'expectedIncludes' => [
            'property' => [],
            'nested' => ['*'],
        ],
    ];

    yield 'without property inclusion' => [
        'lazyDataAllowedIncludes' => null,
        'dataAllowedIncludes' => ['nested', 'property'],
        'requestedIncludes' => null,
        'expectedIncludes' => null,
    ];
});
