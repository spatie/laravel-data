<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\handleExceptions;
use function Pest\Laravel\postJson;

use Spatie\LaravelData\Attributes\WithoutValidation;

use Spatie\LaravelData\Data;
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


it('always validates requests when passed to the from method', function () {
    RequestData::clear();

    try {
        RequestData::from(new Request());
    } catch (ValidationException $exception) {
        expect($exception->errors())->toMatchArray([
            'string' => [__('validation.required', ['attribute' => 'string'])],
        ]);

        return;
    }

    $this->fail('We should not end up here');
});

it('can check for authorization', function () {
    RequestData::$enableAuthorizeFailure = true;

    performRequest('Hello')->assertStatus(403);
});

it(
    'can manually override how the data object will be constructed',
    function () {
    class TestOverrideableDataFromRequest extends Data
    {
        public function __construct(
            #[WithoutValidation]
            public string $name
        ) {
        }

        public static function fromRequest(Request $request)
        {
            return new self("{$request->input('first_name')} {$request->input('last_name')}");
        }
    }

    Route::post('/other-route', function (\TestOverrideableDataFromRequest $data) {
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
