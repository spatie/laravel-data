<?php

namespace Spatie\LaravelData\Tests\Support\EloquentCasts;

use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Tests\Fakes\DummyModelWithCasts;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;

class DataEloquentCastTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        DummyModelWithCasts::migrate();
    }

    /** @test */
    public function it_can_save_a_data_object()
    {
        DummyModelWithCasts::create([
            'data' => new SimpleData('Test'),
        ]);

        $this->assertDatabaseHas(DummyModelWithCasts::class, [
            'data' => json_encode(['string' => 'Test']),
        ]);
    }

    /** @test */
    public function it_can_save_a_data_object_as_an_array()
    {
        DummyModelWithCasts::create([
            'data' => ['string' => 'Test'],
        ]);

        $this->assertDatabaseHas(DummyModelWithCasts::class, [
            'data' => json_encode(['string' => 'Test']),
        ]);
    }

    /** @test */
    public function it_can_load_a_data_object()
    {
        DB::table('dummy_model_with_casts')->insert([
            'data' => json_encode(['string' => 'Test']),
        ]);

        /** @var \Spatie\LaravelData\Tests\Fakes\DummyModelWithCasts $model */
        $model = DummyModelWithCasts::first();

        $this->assertEquals(
            new SimpleData('Test'),
            $model->data
        );
    }

    /** @test */
    public function it_can_save_a_null_as_a_value()
    {
        DummyModelWithCasts::create([
            'data' => null,
        ]);

        $this->assertDatabaseHas(DummyModelWithCasts::class, [
            'data' => null,
        ]);
    }

    /** @test */
    public function it_can_load_null_as_a_value()
    {
        DB::table('dummy_model_with_casts')->insert([
            'data' => null,
        ]);

        /** @var \Spatie\LaravelData\Tests\Fakes\DummyModelWithCasts $model */
        $model = DummyModelWithCasts::first();

        $this->assertNull($model->data);
    }
}
