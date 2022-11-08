<?php

namespace Spatie\LaravelData\Tests;

use Closure;
use Generator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Tests\Fakes\CustomDataCollection;
use Spatie\LaravelData\Tests\Fakes\CustomPaginatedDataCollection;
use Spatie\LaravelData\Tests\Fakes\DefaultLazyData;
use Spatie\LaravelData\Tests\Fakes\LazyData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

class DataCollectionTest extends TestCase
{
    /** @test */
    public function it_can_get_a_paginated_data_collection()
    {
        $items = Collection::times(100, fn (int $index) => "Item {$index}");

        $paginator = new LengthAwarePaginator(
            $items->forPage(1, 15),
            100,
            15
        );

        $collection = SimpleData::collection($paginator);

        $this->assertInstanceOf(PaginatedDataCollection::class, $collection);
        $this->assertMatchesJsonSnapshot($collection->toArray());
    }

    /** @test */
    public function it_can_get_a_paginated_cursor_data_collection()
    {
        $items = Collection::times(100, fn (int $index) => "Item {$index}");

        $paginator = new CursorPaginator(
            $items,
            15,
        );

        $collection = SimpleData::collection($paginator);

        if (version_compare(app()->version(), '9.0.0', '<=')) {
            $this->markTestIncomplete('Laravel 8 uses a different format');
        }

        $this->assertInstanceOf(CursorPaginatedDataCollection::class, $collection);
        $this->assertMatchesJsonSnapshot($collection->toArray());
    }


    /** @test */
    public function a_collection_can_be_constructed_with_data_objects()
    {
        $collectionA = SimpleData::collection([
            SimpleData::from('A'),
            SimpleData::from('B'),
        ]);

        $collectionB = SimpleData::collection([
            'A',
            'B',
        ]);

        $this->assertEquals($collectionB->toArray(), $collectionA->toArray());
    }
    
    /** @test */
    public function a_can_see_if_collection_contains()
    {
        $collection = SimpleData::collection(['A', 'B']);
        $contains = $collection->contains(fn (SimpleData $data) => $data->string === 'A')
        $this->assertTrue($filtered);
        
        $doesNotContain = $collection->contains(fn (SimpleData $data) => $data->string === 'C')
        $this->assertFalse($filtered);
    }

    /** @test */
    public function a_collection_can_be_filtered()
    {
        $collection = SimpleData::collection(['A', 'B']);

        $filtered = $collection->filter(fn (SimpleData $data) => $data->string === 'A')->toArray();

        $this->assertEquals([
            ['string' => 'A'],
        ], $filtered);
    }


    /** @test */
    public function a_collection_can_be_transformed()
    {
        $collection = SimpleData::collection(['A', 'B']);

        $filtered = $collection->through(fn (SimpleData $data) => new SimpleData("{$data->string}x"))->toArray();

        $this->assertEquals([
            ['string' => 'Ax'],
            ['string' => 'Bx'],
        ], $filtered);
    }

    /** @test */
    public function a_paginated_collection_can_be_transformed()
    {
        $collection = SimpleData::collection(
            new LengthAwarePaginator(['A', 'B'], 2, 15)
        );

        $filtered = $collection->through(fn (SimpleData $data) => new SimpleData("{$data->string}x"))->toArray();

        $this->assertEquals([
            ['string' => 'Ax'],
            ['string' => 'Bx'],
        ], $filtered['data']);
    }

    /** @test */
    public function it_is_iteratable()
    {
        $collection = SimpleData::collection([
            'A', 'B', 'C', 'D',
        ]);

        $letters = [];

        foreach ($collection as $item) {
            $letters[] = $item->string;
        }

        $this->assertEquals(['A', 'B', 'C', 'D'], $letters);
    }

    /**
     * @test
     * @dataProvider arrayAccessCollections
     */
    public function it_has_array_access(Closure $collection)
    {
        $collection = $collection();

        // Count
        $this->assertEquals(4, count($collection));

        // Offset exists
        $this->assertFalse(empty($collection[3]));

        $this->assertTrue(empty($collection[5]));

        // Offset get
        $this->assertEquals(SimpleData::from('A'), $collection[0]);

        $this->assertEquals(SimpleData::from('D'), $collection[3]);

        if ($collection->items() instanceof AbstractPaginator || $collection->items() instanceof CursorPaginator) {
            return;
        }

        // Offset set
        $collection[2] = 'And now something completely different';
        $collection[4] = 'E';

        $this->assertEquals(SimpleData::from('And now something completely different'), $collection[2]);
        $this->assertEquals(SimpleData::from('E'), $collection[4]);

        // Offset unset
        unset($collection[4]);

        $this->assertCount(4, $collection);
    }

    public function arrayAccessCollections(): Generator
    {
        yield "array" => [
            fn () => SimpleData::collection([
                'A', 'B', SimpleData::from('C'), SimpleData::from('D'),
            ]),
        ];

        yield "collection" => [
            fn () => SimpleData::collection([
                'A', 'B', SimpleData::from('C'), SimpleData::from('D'),
            ]),
        ];
    }

    /** @test */
    public function it_can_dynamically_include_data_based_upon_the_request()
    {
        LazyData::$allowedIncludes = [''];

        $response = LazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request());

        $this->assertEquals([
            [],
            [],
            [],
        ], $response->getData(true));

        LazyData::$allowedIncludes = ['name'];

        $includedResponse = LazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request()->merge([
            'include' => 'name',
        ]));

        $this->assertEquals(
            [
                ['name' => 'Ruben'],
                ['name' => 'Freek'],
                ['name' => 'Brent'],
            ],
            $includedResponse->getData(true)
        );
    }

    /** @test */
    public function it_can_disable_manually_including_data_in_the_request()
    {
        LazyData::$allowedIncludes = [];

        $response = LazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request()->merge([
            'include' => 'name',
        ]));

        $this->assertEquals([
            [],
            [],
            [],
        ], $response->getData(true));

        LazyData::$allowedIncludes = ['name'];

        $response = LazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request()->merge([
            'include' => 'name',
        ]));

        $this->assertEquals([
            ['name' => 'Ruben'],
            ['name' => 'Freek'],
            ['name' => 'Brent'],
        ], $response->getData(true));

        LazyData::$allowedIncludes = null;

        $response = LazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request()->merge([
            'include' => 'name',
        ]));

        $this->assertEquals([
            ['name' => 'Ruben'],
            ['name' => 'Freek'],
            ['name' => 'Brent'],
        ], $response->getData(true));
    }

    /** @test */
    public function it_can_dynamically_exclude_data_based_upon_the_request()
    {
        DefaultLazyData::$allowedExcludes = [];

        $response = DefaultLazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request());

        $this->assertEquals(
            [
                ['name' => 'Ruben'],
                ['name' => 'Freek'],
                ['name' => 'Brent'],
            ],
            $response->getData(true)
        );

        DefaultLazyData::$allowedExcludes = ['name'];

        $excludedResponse = DefaultLazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request()->merge([
            'exclude' => 'name',
        ]));

        $this->assertEquals([
            [],
            [],
            [],
        ], $excludedResponse->getData(true));
    }

    /** @test */
    public function it_can_disable_manually_excluding_data_in_the_request()
    {
        DefaultLazyData::$allowedExcludes = [];

        $response = DefaultLazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request()->merge([
            'exclude' => 'name',
        ]));

        $this->assertEquals([
            ['name' => 'Ruben'],
            ['name' => 'Freek'],
            ['name' => 'Brent'],
        ], $response->getData(true));

        DefaultLazyData::$allowedExcludes = ['name'];

        $response = DefaultLazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request()->merge([
            'exclude' => 'name',
        ]));

        $this->assertEquals([
            [],
            [],
            [],
        ], $response->getData(true));

        DefaultLazyData::$allowedExcludes = null;

        $response = DefaultLazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request()->merge([
            'exclude' => 'name',
        ]));

        $this->assertEquals([
            [],
            [],
            [],
        ], $response->getData(true));
    }

    /** @test */
    public function it_can_update_data_properties_within_a_collection()
    {
        $collection = LazyData::collection([
            LazyData::from('Never gonna give you up!'),
        ]);

        $this->assertEquals([
            ['name' => 'Never gonna give you up!'],
        ], $collection->include('name')->toArray());

        $collection[0]->name = 'Giving Up on Love';

        $this->assertEquals([
            ['name' => 'Giving Up on Love'],
        ], $collection->include('name')->toArray());

        $collection[] = LazyData::from('Cry for help');

        $this->assertEquals([
            ['name' => 'Giving Up on Love'],
            ['name' => 'Cry for help'],
        ], $collection->include('name')->toArray());

        unset($collection[0]);

        $this->assertEquals([
            1 => ['name' => 'Cry for help'],
        ], $collection->include('name')->toArray());
    }

    /** @test */
    public function it_supports_lazy_collections()
    {
        $lazyCollection = new LazyCollection(function () {
            $items = [
                'Never gonna give you up!',
                'Giving Up on Love',
            ];

            foreach ($items as $item) {
                yield $item;
            }
        });

        $collection = SimpleData::collection($lazyCollection);

        $this->assertEquals([
            SimpleData::from('Never gonna give you up!'),
            SimpleData::from('Giving Up on Love'),
        ], $collection->items());

        $transformed = $collection->through(function (SimpleData $data) {
            $data->string = strtoupper($data->string);

            return $data;
        })->filter(fn (SimpleData $data) => $data->string === strtoupper('Never gonna give you up!'))->toArray();

        $this->assertEquals([
            ['string' => strtoupper('Never gonna give you up!')],
        ], $transformed);
    }

    /** @test */
    public function it_can_convert_a_data_collection_into_a_laravel_collection()
    {
        $this->assertEquals(
            collect([
                SimpleData::from('A'),
                SimpleData::from('B'),
                SimpleData::from('C'),
            ]),
            SimpleData::collection(['A', 'B', 'C'])->toCollection()
        );
    }

    /** @test */
    public function a_collection_can_be_transformed_to_json()
    {
        $collection = SimpleData::collection(['A', 'B', 'C']);

        $this->assertEquals('[{"string":"A"},{"string":"B"},{"string":"C"}]', $collection->toJson());
        $this->assertEquals('[{"string":"A"},{"string":"B"},{"string":"C"}]', json_encode($collection));
    }

    /** @test */
    public function it_will_cast_data_object_into_the_data_collection_objects()
    {
        $dataClass = new class ('') extends Data {
            public function __construct(public string $otherString)
            {
            }

            public static function fromSimpleData(SimpleData $simpleData): static
            {
                return new self($simpleData->string);
            }
        };

        $collection = $dataClass::collection([
            SimpleData::from('A'),
            SimpleData::from('B'),
        ]);

        $this->assertInstanceOf($dataClass::class, $collection[0]);
        $this->assertEquals('A', $collection[0]->otherString);

        $this->assertInstanceOf($dataClass::class, $collection[1]);
        $this->assertEquals('B', $collection[1]->otherString);
    }

    public function it_can_reset_the_keys()
    {
        $collection = SimpleData::collection([
            1 => SimpleData::from('a'),
            3 => SimpleData::from('b'),
        ]);

        $this->assertEquals(
            SimpleData::collection([
                0 => SimpleData::from('a'),
                1 => SimpleData::from('b'),
            ]),
            $collection->values()
        );
    }

    /** @test */
    public function it_can_use_magical_creation_methods_to_create_a_collection()
    {
        $collection = SimpleData::collection(['A', 'B']);

        $this->assertEquals(
            [SimpleData::from('A'), SimpleData::from('B')],
            $collection->toCollection()->all()
        );
    }

    /** @test */
    public function it_can_return_a_custom_data_collection_when_collecting_data()
    {
        $class = new class ('') extends Data {
            protected static string $collectionClass = CustomDataCollection::class;

            public function __construct(public string $string)
            {
            }
        };

        $collection = $class::collection([
            ['string' => 'A'],
            ['string' => 'B'],
        ]);

        $this->assertInstanceOf(CustomDataCollection::class, $collection);
    }

    /** @test */
    public function it_can_return_a_custom_paginated_data_collection_when_collecting_data()
    {
        $class = new class ('') extends Data {
            protected static string $paginatedCollectionClass = CustomPaginatedDataCollection::class;

            public function __construct(public string $string)
            {
            }
        };

        $collection = $class::collection(new LengthAwarePaginator([['string' => 'A'], ['string' => 'B']], 2, 15));

        $this->assertInstanceOf(CustomPaginatedDataCollection::class, $collection);
    }

    /**
     * @test
     * @dataProvider collectionOperationsProvider
     */
    public function it_can_perform_some_collection_operations(
        string $operation,
        array $arguments,
        array $expected,
    ) {
        $collection = SimpleData::collection(['A', 'B', 'C']);

        $changedCollection = $collection->{$operation}(...$arguments);

        $this->assertEquals(
            $expected,
            $changedCollection->toArray(),
        );
    }

    public function collectionOperationsProvider(): Generator
    {
        yield [
            'operation' => 'filter',
            'arguments' => [fn (SimpleData $data) => $data->string !== 'B'],
            'expected' => [0 => ['string' => 'A'],2 => ['string' => 'C']],
        ];
    }
}
