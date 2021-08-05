<?php

namespace Spatie\LaravelData\Tests\Support\EloquentCasts;

use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Tests\Fakes\DummyModel;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;

class DataCollectionEloquentCastTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        DummyModel::migrate();
    }

    /** @test */
    public function it_can_save_a_data_collection()
    {
        DummyModel::create([
            'data_collection' => SimpleData::collection([
                new SimpleData('Hello'),
                new SimpleData('World'),
            ]),
        ]);

        $this->assertDatabaseHas(DummyModel::class, [
            'data_collection' => json_encode([
                ['string' => 'Hello'],
                ['string' => 'World'],
            ]),
        ]);
    }

    /** @test */
    public function it_can_save_a_data_object_as_an_array()
    {
        DummyModel::create([
            'data_collection' => [
                ['string' => 'Hello'],
                ['string' => 'World'],
            ],
        ]);

        $this->assertDatabaseHas('dummy_models', [
            'data_collection' => json_encode([
                ['string' => 'Hello'],
                ['string' => 'World'],
            ]),
        ]);
    }

    /** @test */
    public function it_can_load_a_data_object()
    {
        DB::table('dummy_models')->insert([
            'data_collection' => json_encode([
                ['string' => 'Hello'],
                ['string' => 'World'],
            ]),
        ]);

        /** @var \Spatie\LaravelData\Tests\Fakes\DummyModel $model */
        $model = DummyModel::first();

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
        DummyModel::create([
            'data_collection' => null,
        ]);

        $this->assertDatabaseHas('dummy_models', [
            'data_collection' => null,
        ]);
    }

    /** @test */
    public function it_can_load_null_as_a_value()
    {
        DB::table('dummy_models')->insert([
            'data_collection' => null,
        ]);

        /** @var \Spatie\LaravelData\Tests\Fakes\DummyModel $model */
        $model = DummyModel::first();

        $this->assertNull($model->data_collection);
    }
}
