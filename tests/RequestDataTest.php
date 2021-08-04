<?php

namespace Spatie\LaravelData\Tests;

use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Tests\Factories\DataBlueprintFactory;
use Spatie\LaravelData\Tests\Factories\DataPropertyBlueprintFactory;
use Spatie\LaravelData\Tests\Fakes\RequestData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

class RequestDataTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->handleValidationExceptions();

        RequestData::clear();

        Route::post('/example-route', function (RequestData $data) {
            return ['given' => $data->string];
        });
    }

    /** @test */
    public function it_can_pass_validation()
    {
        $this->validRequest()
            ->assertOk()
            ->assertJson(['given' => 'Hello']);
    }

    /** @test */
    public function it_can_fail_validation()
    {
        $this->invalidRequest()
            ->assertStatus(422)
            ->assertJsonValidationErrors(['string' => 'The string must not be greater than 10 characters.']);
    }

    /** @test */
    public function it_can_overwrite_validation_rules()
    {
        RequestData::$rules = ['string' => 'max:200'];

        $this->invalidRequest()
            ->assertOk()
            ->assertJson(['given' => 'Hello world']);
    }

    /** @test */
    public function it_can_overwrite_validation_messages()
    {
        RequestData::$messages = [
            'max' => 'too long',
        ];

        $this->invalidRequest()
            ->assertStatus(422)
            ->assertJsonValidationErrors(['string' => 'too long']);
    }


    /** @test */
    public function it_can_overwrite_validation_attributes()
    {
        RequestData::$attributes = [
            'string' => 'data property',
        ];

        $this->invalidRequest()
            ->assertStatus(422)
            ->assertJsonValidationErrors(['string' => 'The data property must not be greater than 10 characters.']);
    }

    /** @test */
    public function it_can_change_the_validator()
    {
        RequestData::$validatorClosure = fn(Validator $validator) => $validator->setRules([]);

        $this->invalidRequest()
            ->assertOk()
            ->assertJson(['given' => 'Hello world']);
    }

    /** @test */
    public function it_can_nest_data()
    {
        DataBlueprintFactory::new('SingleNestedData')->withProperty(
            DataPropertyBlueprintFactory::new('simple')->withType(SimpleData::class)
        )->create();

        Route::post('/nested-route', function (\SingleNestedData $data) {
            return ['given' => $data->simple->string];
        });

        $this->postJson('/nested-route', [
            'simple' => [
                'string' => 'Hello World',
            ],
        ])
            ->assertOk()
            ->assertSee('Hello World');

        $this->postJson('/nested-route', [
            'simple' => [
                'string' => 5333,
            ],
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['simple.string' => 'The simple.string must be a string.']);
    }

    /** @test */
    public function it_can_nest_collections_of_data()
    {
        DataBlueprintFactory::new('CollectionNestedData')->withProperty(
            DataPropertyBlueprintFactory::dataCollection('simple_collection', SimpleData::class)
        )->create();

        Route::post('/nested-route', function (\CollectionNestedData $data) {
            return ['given' => $data->simple_collection->all()];
        });

        $this->postJson('/nested-route', [
            'simple_collection' => [
                [
                    'string' => 'Hello World',
                ],
                [
                    'string' => 'Goodbye',
                ],
            ],
        ])
            ->assertOk()
            ->assertJson([
                'given' => [
                    [
                        'string' => 'Hello World',
                    ],
                    [
                        'string' => 'Goodbye',
                    ],
                ],
            ]);

        $this->postJson('/nested-route', [
            'simple_collection' => [
                [
                    'string' => 'Hello World',
                ],
                [
                    'string' => 3.14,
                ],
            ],
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['simple_collection.1.string' => 'The simple_collection.1.string must be a string.']);
    }

    private function validRequest(): TestResponse
    {
        return $this->postJson('/example-route', [
            'string' => 'Hello',
        ]);
    }

    private function invalidRequest(): TestResponse
    {
        return $this->postJson('/example-route', [
            'string' => 'Hello world',
        ]);
    }
}
