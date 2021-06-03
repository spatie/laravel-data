<?php

namespace Spatie\LaravelData\Tests;

use Illuminate\Foundation\Auth\User;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Tests\Fakes\LazyData;
use Spatie\LaravelData\Tests\Fakes\ParentData;
use Spatie\LaravelData\Tests\Fakes\UserResource;

class DataResourceTest extends TestCase
{
    /** @test */
    public function it_can_create_a_resource()
    {
        $user = $this->makeUser();

        $resource = UserResource::create($user);

        $this->assertEquals([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ], $resource->toArray());
    }

    /** @test */
    public function it_can_create_a_collection_of_resources()
    {
        $collection = UserResource::collection(collect([
            $user1 = $this->makeUser(),
            $user2 = $this->makeUser(),
            $user3 = $this->makeUser(),
        ]));

        $this->assertEquals([
            [
                "id" => $user1->id,
                "name" => $user1->name,
                "email" => $user1->email,
            ],
            [
                "id" => $user2->id,
                "name" => $user2->name,
                "email" => $user2->email,
            ],
            [
                "id" => $user3->id,
                "name" => $user3->name,
                "email" => $user3->email,
            ],
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

    private function makeUser(): User
    {
        return User::make([
            'id' => $this->faker()->numberBetween(),
            'name' => $this->faker()->name,
            'email' => $this->faker()->email,
        ]);
    }
}
