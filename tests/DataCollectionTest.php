<?php

namespace Spatie\LaravelData\Tests;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

class DataCollectionTest extends TestCase
{
    /** @test */
    public function it_can_get_a_paginated_data_collection()
    {
        $items = Collection::times(100, fn(int $index) => "Item {$index}");

        $paginator = new LengthAwarePaginator(
            $items->forPage(1, 15),
            100,
            15
        );

        $this->assertMatchesSnapshot(SimpleData::collection($paginator)->toArray());
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

        $filtered = $collection->filter(fn(SimpleData $data) => $data->string === 'A')->toArray();

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

        $filtered = $collection->filter(fn(SimpleData $data) => $data->string === 'A')->toArray();

        $this->assertEquals([
            ['string' => 'A'],
            ['string' => 'B'],
        ], $filtered['data']);
    }

    /** @test */
    public function a_collection_can_be_transformed()
    {
        $collection = SimpleData::collection(['A', 'B']);

        $filtered = $collection->through(fn(SimpleData $data) => new SimpleData("{$data->string}x"))->toArray();

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

        $filtered = $collection->through(fn(SimpleData $data) => new SimpleData("{$data->string}x"))->toArray();

        $this->assertEquals([
            ['string' => 'Ax'],
            ['string' => 'Bx'],
        ], $filtered['data']);
    }
}
