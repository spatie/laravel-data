<?php

namespace Spatie\LaravelData\Tests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Tests\Factories\DataBlueprintFactory;
use Spatie\LaravelData\Tests\Factories\DataMagicMethodFactory;
use Spatie\LaravelData\Tests\Factories\DataPropertyBlueprintFactory;
use Spatie\LaravelData\Tests\Fakes\RequestData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Undefined;

class RequestDataTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->handleExceptions([
            AuthenticationException::class,
            AuthorizationException::class,
            ValidationException::class,
        ]);

        RequestData::clear();

        Route::post('/example-route', function (RequestData $data) {
            return ['given' => $data->string];
        });
    }

    /** @test */
    public function it_can_pass_validation()
    {
        $this->performRequest('Hello')
            ->assertOk()
            ->assertJson(['given' => 'Hello']);
    }

    /** @test */
    public function it_can_fail_validation()
    {
        $this->performRequest('Hello World')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['string' => __('validation.max.string', ['attribute' => 'string', 'max' => 10])]);
    }

    /** @test */
    public function it_can_overwrite_validation_rules()
    {
        RequestData::$rules = ['string' => 'max:200'];

        $this->performRequest('Accepted string longer then 10 characters from attribute on data object')
            ->assertOk()
            ->assertJson(['given' => 'Accepted string longer then 10 characters from attribute on data object']);
    }

    /** @test */
    public function it_can_overwrite_rules_like_a_regular_laravel_request()
    {
        RequestData::$rules = ['string' => 'min:10|numeric'];

        $this->performRequest('Too short')
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'string' => [
                    __('validation.min.string', ['attribute' => 'string', 'min' => 10]),
                    __('validation.numeric', ['attribute' => 'string']),
                ],
            ]);

        RequestData::$rules = ['string' => ['min:10', 'numeric']];

        $this->performRequest('Too short')
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'string' => [
                    __('validation.min.string', ['attribute' => 'string', 'min' => 10]),
                    __('validation.numeric', ['attribute' => 'string']),
                ],
            ]);

        RequestData::$rules = ['string' => Rule::in(['alpha', 'beta'])];

        $this->performRequest('Not in list')
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'string' => __('validation.in', ['attribute' => 'string']),
            ]);
    }

    /** @test */
    public function it_can_overwrite_validation_messages()
    {
        RequestData::$messages = [
            'max' => 'too long',
        ];

        $this->performRequest('Hello World')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['string' => 'too long']);
    }


    /** @test */
    public function it_can_overwrite_validation_attributes()
    {
        RequestData::$attributes = [
            'string' => 'data property',
        ];

        $this->performRequest('Hello world')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['string' => __('validation.max.string', ['attribute' => 'data property', 'max' => 10])]);
    }

    /** @test */
    public function it_can_change_the_validator()
    {
        RequestData::$validatorClosure = fn (Validator $validator) => $validator->setRules([]);

        $this->performRequest('Hello world')
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

    /** @test */
    public function it_can_check_for_authorisation()
    {
        RequestData::$enableAuthorizeFailure = true;

        $this->performRequest('Hello')->assertStatus(403);
    }

    /** @test */
    public function it_can_skip_validation_on_certain_properties()
    {
        DataBlueprintFactory::new('ValidationSkippeableDataFromRequest')
            ->withProperty(
                DataPropertyBlueprintFactory::new('first_name')
                ->withType('string')
            )
            ->withProperty(
                DataPropertyBlueprintFactory::new('last_name')
                ->withAttribute(WithoutValidation::class)
                ->withAttribute(Max::class, [2])
                ->withType('string')
            )
            ->create();

        Route::post('/other-route', function (\ValidationSkippeableDataFromRequest $data) {
            return ['first_name' => $data->first_name, 'last_name' => $data->last_name];
        });

        $this->postJson('/other-route', [
            'first_name' => 'Rick', 'last_name' => 'Astley',
        ])
            ->assertOk()
            ->assertJson(['first_name' => 'Rick', 'last_name' => 'Astley']);
    }

    /** @test */
    public function it_can_manually_override_how_the_data_object_will_be_constructed()
    {
        DataBlueprintFactory::new('OverrideableDataFromRequest')
            ->withProperty(
                DataPropertyBlueprintFactory::new('name')
                ->withAttribute(WithoutValidation::class)
                ->withType('string')
            )
            ->withMethod(
                DataMagicMethodFactory::new('fromRequest')
                    ->withInputType(Request::class, 'request')
                    ->withBody('return new self("{$request->input(\'first_name\')} {$request->input(\'last_name\')}");')
            )
            ->create();

        Route::post('/other-route', function (\OverrideableDataFromRequest $data) {
            return ['name' => $data->name];
        });

        $this->postJson('/other-route', [
            'first_name' => 'Rick',
            'last_name' => 'Astley',
        ])
            ->assertOk()
            ->assertJson(['name' => 'Rick Astley']);
    }

    /** @test */
    public function it_wont_validate_undefined_properties()
    {
        DataBlueprintFactory::new('UndefinableDataFromRequest')
            ->withProperty(
                DataPropertyBlueprintFactory::new('name')
                    ->withType('string'),
                DataPropertyBlueprintFactory::new('age')
                    ->withType('int', Undefined::class)
            )
            ->create();

        Route::post('/other-route', function (\UndefinableDataFromRequest $data) {
            return $data->toArray();
        });

        $this->postJson('/other-route', [
            'name' => 'Rick Astley',
            'age' => 42,
        ])
            ->assertOk()
            ->assertJson(['name' => 'Rick Astley', 'age' => 42]);

        $this->postJson('/other-route', [
            'name' => 'Rick Astley',
        ])
            ->assertOk()
            ->assertJson(['name' => 'Rick Astley']);
    }

    private function performRequest(string $string): TestResponse
    {
        return $this->postJson('/example-route', [
            'string' => $string,
        ]);
    }
}
