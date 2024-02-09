<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\handleExceptions;
use function Pest\Laravel\postJson;

use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithExplicitValidationRuleAttributeData;

beforeEach(function () {
    handleExceptions([
        AuthenticationException::class,
        AuthorizationException::class,
        ValidationException::class,
    ]);

    Route::post('/example-route', function (Sim $data) {
        return ['given' => $data->string];
    });
});

it('can pass validation', function () {
    Route::post('/example-route', function (SimpleData $data) {
        return ['given' => $data->string];
    });

    postJson('/example-route', [
        'string' => 'Hello',
    ])
        ->assertOk()
        ->assertJson(['given' => 'Hello']);
});

it('can returns a 201 response code for POST requests', function () {
    Route::post('/example-route', function () {
        return new SimpleData(request()->input('string'));
    });

    postJson('/example-route', [
        'string' => 'Hello',
    ])
        ->assertCreated()
        ->assertJson(['string' => 'Hello']);
});

it('is possible to overwrite the status response code', function () {
    Route::post('/example-route', function () {
        return new class (request()->input('string')) extends SimpleData {
            protected function calculateResponseStatus(Request $request): int
            {
                return 301;
            }
        };
    });

    postJson('/example-route', [
        'string' => 'Hello',
    ])
        ->assertStatus(301)
        ->assertJson(['string' => 'Hello']);
});

it('can fail validation', function () {
    Route::post('/example-route', function (SimpleDataWithExplicitValidationRuleAttributeData $data) {
        return ['email' => $data->email];
    });

    postJson('/example-route', [
        'email' => 'Hello',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors([
            'email' => __('validation.email', ['attribute' => 'email']),
        ]);
});

it('always validates requests when passed to the from method', function () {
    try {
        SimpleData::from(new Request());
    } catch (ValidationException $exception) {
        expect($exception->errors())->toMatchArray([
            'string' => [__('validation.required', ['attribute' => 'string'])],
        ]);

        return;
    }

    $this->fail('We should not end up here');
});

it('can check for authorization', function () {
    class TestDataWithAuthorizationFailure extends Data
    {
        public string $string;

        public static function authorize()
        {
            return false;
        }
    }

    Route::post('/example-route', function (\TestDataWithAuthorizationFailure $data) {
    });

    postJson('/example-route', [
        'string' => 'test',
    ])->assertStatus(403);
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
