<?php

namespace Spatie\LaravelData\Tests\Support\EloquentCasts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelData\Support\EloquentCasts\DataEloquentCast;
use Spatie\LaravelData\Tests\Fakes\DummyModel;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;

class DataEloquentCastTest extends TestCase
{
    public function setUp() : void
    {
        parent::setUp();

        DummyModel::migrate();
    }

    /** @test */
    public function it_can_save_a_data_object()
    {
        DummyModel::create([
            'data' => new SimpleData('Test')
        ]);

        $this->assertDatabaseHas(DummyModel::class, [
            'data' => json_encode(['string' => 'Test'])
        ]);
    }

    /** @test */
    public function it_can_save_a_data_object_as_an_array()
    {
        DummyModel::create([
            'data' => ['string' => 'Test']
        ]);

        $this->assertDatabaseHas(DummyModel::class, [
            'data' => json_encode(['string' => 'Test'])
        ]);
    }

    /** @test */
    public function it_can_load_a_data_object()
    {
        DB::table('dummy_models')->insert([
            'data' => json_encode(['string' => 'Test'])
        ]);

        /** @var \Spatie\LaravelData\Tests\Fakes\DummyModel $model */
        $model = DummyModel::first();

        $this->assertEquals(
            new SimpleData('Test'),
            $model->data
        );
    }

    /** @test */
    public function it_can_save_a_null_as_a_value()
    {
        DummyModel::create([
            'data' => null
        ]);

        $this->assertDatabaseHas(DummyModel::class, [
            'data' => null
        ]);
    }

    /** @test */
    public function it_can_load_null_as_a_value()
    {
        DB::table('dummy_models')->insert([
            'data' => null
        ]);

        /** @var \Spatie\LaravelData\Tests\Fakes\DummyModel $model */
        $model = DummyModel::first();

        $this->assertNull($model->data);
    }
}
