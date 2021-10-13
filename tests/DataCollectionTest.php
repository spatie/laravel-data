<?php

namespace Spatie\LaravelData\Tests;

use Closure;
use Generator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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

        $this->assertMatchesJsonSnapshot(SimpleData::collection($paginator)->toArray());
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
    public function a_collection_can_be_filtered()
    {
        $collection = SimpleData::collection(['A', 'B']);

        $filtered = $collection->filter(fn (SimpleData $data) => $data->string === 'A')->toArray();

        $this->assertEquals([
            ['string' => 'A'],
        ], $filtered);
    }

    /** @test */
    public function a_paginated_collection_cannot_be_filtered()
    {
        $collection = SimpleData::collection(
            new LengthAwarePaginator(['A', 'B'], 2, 15)
        );

        $filtered = $collection->filter(fn (SimpleData $data) => $data->string === 'A')->toArray();

        $this->assertEquals([
            ['string' => 'A'],
            ['string' => 'B'],
        ], $filtered['data']);
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

        yield "paginator" => [
            fn () => SimpleData::collection(new LengthAwarePaginator([
                'A', 'B', SimpleData::from('C'), SimpleData::from('D'),
            ], 4, 15)),
        ];

        yield "cursor paginator" => [
            fn () => SimpleData::collection(new CursorPaginator([
                'A', 'B', SimpleData::from('C'), SimpleData::from('D'),
            ], 4)),
        ];
    }

    /** @test */
    public function it_can_dynamically_include_data_based_upon_the_request()
    {
        $response = LazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request());

        $includedResponse = LazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request()->merge([
            'include' => 'name',
        ]));

        $this->assertEquals([
            [],
            [],
            [],
        ], $response->getData(true));

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
        $response = DefaultLazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request());

        $excludedResponse = DefaultLazyData::collection(['Ruben', 'Freek', 'Brent'])->toResponse(request()->merge([
            'exclude' => 'name',
        ]));

        $this->assertEquals(
            [
                ['name' => 'Ruben'],
                ['name' => 'Freek'],
                ['name' => 'Brent'],
            ],
            $response->getData(true)
        );

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
}
