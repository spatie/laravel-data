<?php

namespace Spatie\LaravelData\Tests;

use Illuminate\Foundation\Auth\User;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Tests\Fakes\DefaultLazyData;
use Spatie\LaravelData\Tests\Fakes\EmptyData;
use Spatie\LaravelData\Tests\Fakes\LazyData;
use Spatie\LaravelData\Tests\Fakes\LazyWhenData;
use Spatie\LaravelData\Tests\Fakes\ParentData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

class DataResourceTest extends TestCase
{
    /** @test */
    public function it_can_create_a_resource()
    {
        $resource = SimpleData::create('Ruben');

        $this->assertEquals([
            'string' => 'Ruben',
        ], $resource->toArray());
    }

    /** @test */
    public function it_can_create_a_collection_of_resources()
    {
        $collection = SimpleData::collection(collect([
            'Ruben',
            'Freek',
            'Brent',
        ]));

        $this->assertEquals([
            ['string' => 'Ruben'],
            ['string' => 'Freek'],
            ['string' => 'Brent'],
        ], $collection->toArray());
    }

    /** @test */
    public function it_can_include_a_lazy_property()
    {
        $resource = new LazyData(
            Lazy::create(fn() => 'test')
        );

        $this->assertEquals([], $resource->toArray());

        $this->assertEquals([
            'name' => 'test',
        ], $resource->include('name')->toArray());
    }

    /** @test */
    public function it_can_have_a_pre_filled_in_lazy_property()
    {
        $resource = new LazyData('test');

        $this->assertEquals([
            'name' => 'test',
        ], $resource->toArray());

        $this->assertEquals([
            'name' => 'test',
        ], $resource->include('name')->toArray());
    }

    /** @test */
    public function it_can_include_a_nested_lazy_property()
    {
        $data = new ParentData(
            Lazy::create(fn() => LazyData::create('Hello')),
            Lazy::create(fn() => LazyData::collection([
                'is', 'it', 'me', 'your', 'looking', 'for',
            ])),
        );

        $this->assertEquals([], (clone $data)->toArray());

        $this->assertEquals([
            'data' => [],
        ], (clone $data)->include('data')->toArray());

        $this->assertEquals([
            'data' => ['name' => 'Hello'],
        ], (clone $data)->include('data.name')->toArray());

        $this->assertEquals([
            'collection' => [
                [],
                [],
                [],
                [],
                [],
                [],
            ],
        ], (clone $data)->include('collection')->toArray());

        $this->assertEquals([
            'collection' => [
                ['name' => 'is'],
                ['name' => 'it'],
                ['name' => 'me'],
                ['name' => 'your'],
                ['name' => 'looking'],
                ['name' => 'for'],
            ],
        ], (clone $data)->include('collection.name')->toArray());
    }

    /** @test */
    public function it_can_have_conditional_lazy_data()
    {
        $data = LazyWhenData::create('Freek');

        $this->assertEquals([], $data->toArray());

        $data = LazyWhenData::create('Ruben');

        $this->assertEquals(['name' => 'Ruben'], $data->toArray());
    }

    /** @test */
    public function it_can_have_conditional_lazy_data_manually_loaded()
    {
        $data = LazyWhenData::create('Freek');

        $this->assertEquals(['name' => 'Freek'], $data->include('name')->toArray());
    }

    /** @test */
    public function it_can_have_default_included_lazy_data()
    {
        $data = DefaultLazyData::create('Freek');

        $this->assertEquals(['name' => 'Freek'], $data->toArray());
    }

    /** @test */
    public function it_can_exclude_default_lazy_data()
    {
        $data = DefaultLazyData::create('Freek');

        $this->assertEquals([], $data->exclude('name')->toArray());
    }

    /** @test */
    public function it_can_get_the_empty_version_of_a_data_object()
    {
        $this->assertEquals([
            'property' => null,
            'lazyProperty' => null,
            'array' => [],
            'collection' => [],
            'dataCollection' => [],
            'data' => [
                'string' => null
            ],
            'lazyData' => [
                'string' => null
            ],
            'defaultProperty' => true
        ], EmptyData::empty());
    }

    /** @test */
    public function it_can_overwrite_properties_in_an_empty_version_of_a_data_object()
    {
        $this->assertEquals([
            'string' => null
        ], SimpleData::empty());

        $this->assertEquals([
            'string' => 'Ruben'
        ], SimpleData::empty(['string' => 'Ruben']));
    }

}
