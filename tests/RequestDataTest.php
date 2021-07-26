<?php

namespace Spatie\LaravelData\Tests;

use Illuminate\Support\Facades\Route;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

class RequestDataTest extends TestCase
{
    public function setUp() : void
    {
        parent::setUp();

        $this->handleValidationExceptions();
    }

    /** @test */
    public function it_can_pass_and_fail_validation()
    {
        Route::post('/example-route', function (SimpleData $data) {
            return ['given' => $data->string];
        });

        $this->postJson('/example-route', [
            'string' => 'Hello',
        ])
            ->assertOk()
            ->assertJson(['given' => 'Hello']);

        $this->postJson('/example-route', [
            'string' => 'Hello world',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['string']);
    }
}
