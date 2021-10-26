<?php

namespace Spatie\LaravelData\Tests\Support\EloquentCasts;

use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Tests\Fakes\DummyModelWithCasts;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;

class DataCollectionEloquentCastTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        DummyModelWithCasts::migrate();
    }

    /** @test */
    public function it_can_save_a_data_collection()
    {
        DummyModelWithCasts::create([
            'data_collection' => SimpleData::collection([
                new SimpleData('Hello'),
                new SimpleData('World'),
            ]),
        ]);

        $this->assertDatabaseHas(DummyModelWithCasts::class, [
            'data_collection' => json_encode([
                ['string' => 'Hello'],
                ['string' => 'World'],
            ]),
        ]);
    }

    /** @test */
    public function it_can_save_a_data_object_as_an_array()
    {
        DummyModelWithCasts::create([
            'data_collection' => [
                ['string' => 'Hello'],
                ['string' => 'World'],
            ],
        ]);

        $this->assertDatabaseHas(DummyModelWithCasts::class, [
            'data_collection' => json_encode([
                ['string' => 'Hello'],
                ['string' => 'World'],
            ]),
        ]);
    }

    /** @test */
    public function it_can_load_a_data_object()
    {
        DB::table('dummy_model_with_casts')->insert([
            'data_collection' => json_encode([
                ['string' => 'Hello'],
                ['string' => 'World'],
            ]),
        ]);

        /** @var \Spatie\LaravelData\Tests\Fakes\DummyModelWithCasts $model */
        $model = DummyModelWithCasts::first();

        $this->assertEquals(
            SimpleData::collection([
                new SimpleData('Hello'),
                new SimpleData('World'),
            ]),
            $model->data_collection
        );
    }

    /** @test */
    public function it_can_save_a_null_as_a_value()
    {
        DummyModelWithCasts::create([
            'data_collection' => null,
        ]);

        $this->assertDatabaseHas(DummyModelWithCasts::class, [
            'data_collection' => null,
        ]);
    }

    /** @test */
    public function it_can_load_null_as_a_value()
    {
        DB::table('dummy_model_with_casts')->insert([
            'data_collection' => null,
        ]);

        /** @var \Spatie\LaravelData\Tests\Fakes\DummyModelWithCasts $model */
        $model = DummyModelWithCasts::first();

        $this->assertNull($model->data_collection);
    }
}
