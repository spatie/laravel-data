<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

use function Pest\Laravel\handleExceptions;
use function Pest\Laravel\postJson;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Tests\Factories\DataBlueprintFactory;
use Spatie\LaravelData\Tests\Factories\DataMagicMethodFactory;
use Spatie\LaravelData\Tests\Factories\DataPropertyBlueprintFactory;
use Spatie\LaravelData\Tests\Fakes\RequestData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;

function performRequest(string $string): TestResponse
{
    return postJson('/example-route', [
        'string' => $string,
    ]);
}

beforeEach(function () {
    handleExceptions([
        AuthenticationException::class,
        AuthorizationException::class,
        ValidationException::class,
    ]);

    RequestData::clear();

    Route::post('/example-route', function (RequestData $data) {
        return ['given' => $data->string];
    });
});

it('can pass validation', function () {
    performRequest('Hello')
        ->assertOk()
        ->assertJson(['given' => 'Hello']);
});

it('can returns a 201 response code for POST requests', function () {
    Route::post('/example-route', function () {
        return new SimpleData(request()->input('string'));
    });

    performRequest('Hello')
        ->assertCreated()
        ->assertJson(['string' => 'Hello']);
});

it('can fail validation', function () {
    performRequest('Hello World')
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'string' => __(
                'validation.max.string',
                ['attribute' => 'string', 'max' => 10]
            ),
        ]);
});

it('can overwrite validation rules', function () {
    RequestData::$rules = ['string' => 'max:200'];

    performRequest('Accepted string longer then 10 characters from attribute on data object')
        ->assertOk()
        ->assertJson([
            'given' => 'Accepted string longer then 10 characters from attribute on data object',
        ]);
});

it('can overwrite rules like a regular Laravel request', function () {
    RequestData::$rules = ['string' => 'min:10|numeric'];

    performRequest('Too short')
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'string' => [
                __('validation.min.string', ['attribute' => 'string', 'min' => 10]),
                __('validation.numeric', ['attribute' => 'string']),
            ],
        ]);

    RequestData::$rules = ['string' => ['min:10', 'numeric']];

    performRequest('Too short')
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'string' => [
                __('validation.min.string', ['attribute' => 'string', 'min' => 10]),
                __('validation.numeric', ['attribute' => 'string']),
            ],
        ]);

    RequestData::$rules = ['string' => Rule::in(['alpha', 'beta'])];

    performRequest('Not in list')
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'string' => __('validation.in', ['attribute' => 'string']),
        ]);
});

it('can overwrite validation messages', function () {
    RequestData::$messages = [
        'max' => 'too long',
    ];

    performRequest('Hello World')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['string' => 'too long']);
});

it('can overwrite validation attributes', function () {
    RequestData::$attributes = [
        'string' => 'data property',
    ];

    performRequest('Hello world')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['string' => __('validation.max.string', ['attribute' => 'data property', 'max' => 10])]);
});

it('can change the validator', function () {
    RequestData::$validatorClosure = fn (Validator $validator) => $validator->setRules([]);

    performRequest('Hello world')
        ->assertOk()
        ->assertJson(['given' => 'Hello world']);
});

it('can nest data', function () {
    DataBlueprintFactory::new('SingleNestedData')->withProperty(
        DataPropertyBlueprintFactory::new('simple')->withType(SimpleData::class)
    )->create();

    Route::post('/nested-route', function (\SingleNestedData $data) {
        return ['given' => $data->simple->string];
    });

    postJson('/nested-route', [
        'simple' => [
            'string' => 'Hello World',
        ],
    ])
        ->assertOk()
        ->assertSee('Hello World');

    postJson('/nested-route', [
        'simple' => [
            'string' => 5333,
        ],
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['simple.string' => 'The simple.string must be a string.']);
});

it('can nest collections of data', function () {
    DataBlueprintFactory::new('CollectionNestedData')->withProperty(
        DataPropertyBlueprintFactory::dataCollection('simple_collection', SimpleData::class)
    )->create();

    Route::post('/nested-route', function (\CollectionNestedData $data) {
        return ['given' => $data->simple_collection->all()];
    });

    postJson('/nested-route', [
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

    postJson('/nested-route', [
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
        ->assertJsonValidationErrors(['simple_collection.1.string' => 'The simple collection.1.string must be a string.']);
});

it('can check for authorization', function () {
    RequestData::$enableAuthorizeFailure = true;

    performRequest('Hello')->assertStatus(403);
});

it('can skip validation on certain properties', function () {
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

    postJson('/other-route', [
        'first_name' => 'Rick', 'last_name' => 'Astley',
    ])
        ->assertOk()
        ->assertJson(['first_name' => 'Rick', 'last_name' => 'Astley']);
});

it(
    'can manually override how the data object will be constructed',
    function () {
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

        postJson('/other-route', [
            'first_name' => 'Rick',
            'last_name' => 'Astley',
        ])
            ->assertOk()
            ->assertJson(['name' => 'Rick Astley']);
    }
);

it("won't validate optional properties", function () {
    DataBlueprintFactory::new('UndefinableDataFromRequest')
        ->withProperty(
            DataPropertyBlueprintFactory::new('name')
                ->withType('string'),
            DataPropertyBlueprintFactory::new('age')
                ->withType('int', Optional::class)
        )
        ->create();

    Route::post('/other-route', function (\UndefinableDataFromRequest $data) {
        return $data->toArray();
    });

    postJson('/other-route', [
        'name' => 'Rick Astley',
        'age' => 42,
    ])
        ->assertOk()
        ->assertJson(['name' => 'Rick Astley', 'age' => 42]);

    postJson('/other-route', [
        'name' => 'Rick Astley',
    ])
        ->assertOk()
        ->assertJson(['name' => 'Rick Astley']);
});

it('can wrap data', function () {
    Route::post('/example-route', function () {
        return SimpleData::from(request()->input('string'))->wrap('data');
    });

    performRequest('Hello World')
        ->assertCreated()
        ->assertJson(['data' => ['string' => 'Hello World']]);
});

it('can wrap data collections', function () {
    Route::post('/example-route', function () {
        return SimpleData::collection([
            request()->input('string'),
            strtoupper(request()->input('string')),
        ])->wrap('data');
    });

    performRequest('Hello World')
        ->assertCreated()
        ->assertJson([
            'data' => [
                ['string' => 'Hello World'],
                ['string' => 'HELLO WORLD'],
            ],
        ]);
});
