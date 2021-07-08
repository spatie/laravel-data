<?php

namespace Spatie\LaravelData\Tests;

use Generator;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;
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
            SimpleData::create('A'),
            SimpleData::create('B'),
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
    public function it_has_array_access(DataCollection $collection)
    {
        // Count
        $this->assertEquals(4, count($collection));

        // Offset exists
        $this->assertFalse(empty($collection[3]));

        $this->assertTrue(empty($collection[5]));

        // Offset get
        $this->assertEquals(SimpleData::create('A'), $collection[0]);

        $this->assertEquals(SimpleData::create('D'), $collection[3]);

        if ($collection->items() instanceof AbstractPaginator || $collection->items() instanceof CursorPaginator) {
            return;
        }

        // Offset set
        $collection[2] = 'And now something completely different';
        $collection[4] = 'E';

        $this->assertEquals(SimpleData::create('And now something completely different'), $collection[2]);
        $this->assertEquals(SimpleData::create('E'), $collection[4]);

        // Offset unset
        unset($collection[4]);

        $this->assertCount(4, $collection);
    }

    public function arrayAccessCollections(): Generator
    {
        yield "array" => [
            SimpleData::collection([
                'A', 'B', SimpleData::create('C'), SimpleData::create('D'),
            ]),
        ];

        yield "collection" => [
            SimpleData::collection([
                'A', 'B', SimpleData::create('C'), SimpleData::create('D'),
            ]),
        ];

        yield "paginator" => [
            SimpleData::collection(new LengthAwarePaginator([
                'A', 'B', SimpleData::create('C'), SimpleData::create('D'),
            ], 4, 15)),
        ];

        yield "cursor paginator" => [
            SimpleData::collection(new CursorPaginator([
                'A', 'B', SimpleData::create('C'), SimpleData::create('D'),
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
}
