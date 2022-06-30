<?php

namespace Spatie\LaravelData\Tests\Resolvers;

use Generator;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Resolvers\PartialsTreeFromRequestResolver;
use Spatie\LaravelData\Tests\Fakes\LazyData;
use Spatie\LaravelData\Tests\Fakes\MultiLazyData;
use Spatie\LaravelData\Tests\TestCase;

class PartialsTreeFromRequestResolverTest extends TestCase
{
    private PartialsTreeFromRequestResolver $resolver;

    public function setUp(): void
    {
        parent::setUp();

        $this->resolver = resolve(PartialsTreeFromRequestResolver::class);
    }

    /**
     * @test
     * @dataProvider allowedIncludesDataProvider
     */
    public function it_will_correctly_reduce_a_tree_based_upon_allowed_includes(
        ?array $lazyDataAllowedIncludes,
        ?array $dataAllowedIncludes,
        ?string $requestedAllowedIncludes,
        ?array $expectedIncludes
    ) {
        LazyData::$allowedIncludes = $lazyDataAllowedIncludes;

        $data = new class (
            'Hello',
            LazyData::from('Hello'),
            LazyData::collection(['Hello', 'World'])
        ) extends Data {
            public static ?array $allowedIncludes;

            public function __construct(
                public string $property,
                public LazyData $nested,
                #[DataCollectionOf(LazyData::class)]
                public DataCollection $collection,
            ) {
            }

            public static function allowedRequestIncludes(): ?array
            {
                return static::$allowedIncludes;
            }
        };

        $data::$allowedIncludes = $dataAllowedIncludes;

        $request = request();

        if ($requestedAllowedIncludes !== null) {
            $request->merge([
                'include' => $requestedAllowedIncludes,
            ]);
        }

        $trees = $this->resolver->execute($data, $request);

        $this->assertEquals(
            $expectedIncludes,
            $trees->lazyIncluded
        );
    }

    public function allowedIncludesDataProvider(): Generator
    {
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
    }

    /** @test */
    public function it_can_combine_request_and_manual_includes()
    {
        $dataclass = new class(
            Lazy::create(fn() => 'Rick Astley'),
            Lazy::create(fn() => 'Never gonna give you up'),
            Lazy::create(fn() => 1986),
        ) extends MultiLazyData{
            public static function allowedRequestIncludes(): ?array
            {
                return null;
            }
        };

        $data = $dataclass->include('name')->toResponse(request()->merge([
            'include' => 'artist',
        ]))->getData(true);

        $this->assertEquals([
            'artist' => 'Rick Astley',
            'name' => 'Never gonna give you up'
        ], $data);
    }
}
