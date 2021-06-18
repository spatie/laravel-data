<?php

namespace Spatie\LaravelData\Tests;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;
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
            'B'
        ]);

        $this->assertEquals($collectionB->toArray(), $collectionA->toArray());
    }
}
