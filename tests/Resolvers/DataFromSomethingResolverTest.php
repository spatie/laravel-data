<?php

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use Spatie\LaravelData\Resolvers\DataFromSomethingResolver;
use function Pest\Laravel\handleExceptions;
use function Pest\Laravel\mock;
use function Pest\Laravel\postJson;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Tests\Fakes\DataWithMultipleArgumentCreationMethod;

use Spatie\LaravelData\Tests\Fakes\DummyDto;
use Spatie\LaravelData\Tests\Fakes\DummyModel;
use Spatie\LaravelData\Tests\Fakes\DummyModelWithCasts;

beforeEach(function () {
    handleExceptions([ValidationException::class]);
});

it('can create data from a custom method', function () {
    $data = new class ('') extends Data {
        public function __construct(public string $string)
        {
        }

        public static function fromString(string $string): static
        {
            return new self($string);
        }

        public static function fromDto(DummyDto $dto)
        {
            return new self($dto->artist);
        }

        public static function fromArray(array $payload)
        {
            return new self($payload['string']);
        }
    };

    expect($data::from('Hello World'))->toEqual(new $data('Hello World'))
        ->and($data::from(DummyDto::rick()))->toEqual(new $data('Rick Astley'))
        ->and($data::from(DummyDto::rick()))->toEqual(new $data('Rick Astley'))
        ->and($data::from(['string' => 'Hello World']))->toEqual(new $data('Hello World'))
        ->and($data::from(DummyModelWithCasts::make(['string' => 'Hello World'])))->toEqual(new $data('Hello World'));
});

it('can create data from a custom method with an interface parameter', function () {
    $data = new class ('') extends Data {
        public function __construct(public string $string)
        {
        }

        public static function fromInterface(Arrayable $arrayable)
        {
            return new self($arrayable->toArray()['string']);
        }
    };

    $interfaceable = new class () implements Arrayable {
        public function toArray()
        {
            return [
                'string' => 'Rick Astley',
            ];
        }
    };

    expect($data::from($interfaceable))->toEqual(new $data('Rick Astley'));
});

it('can create data from a custom method with an inherit parameter', function () {
    $data = new class ('') extends Data {
        public function __construct(public string $string)
        {
        }

        public static function fromModel(Model $model)
        {
            return new self($model->string);
        }
    };

    $inherited = new DummyModel(['string' => 'Rick Astley']);

    expect($data::from($inherited))->toEqual(new $data('Rick Astley'));
});

it('can resolve validation dependencies for messages', function () {
    $requestMock = mock(Request::class);
    $requestMock->expects('input')->andReturns('value');
    $this->app->bind(Request::class, fn() => $requestMock);

    $data = new class () extends Data {
        public string $name;

        public static function rules()
        {
            return [
                'name' => ['required'],
            ];
        }

        public static function messages(Request $request): array
        {
            return [
                'name.required' => $request->input('key') === 'value' ? 'Name is required' : 'Bad',
            ];
        }
    };

    try {
        $data::validate(['name' => '']);
    } catch (ValidationException $exception) {
        expect($exception->errors())->toMatchArray([
            "name" => [
                "Name is required",
            ],
        ]);

        return;
    }

    $this->fail('We should not end up here');
});

it('can resolve validation dependencies for attributes ', function () {
    $requestMock = mock(Request::class);
    $requestMock->expects('input')->andReturns('value');
    $this->app->bind(Request::class, fn() => $requestMock);

    $data = new class () extends Data {
        public string $name;

        public static function rules()
        {
            return [
                'name' => ['required'],
            ];
        }

        public static function attributes(Request $request): array
        {
            return [
                'name' => $request->input('key') === 'value' ? 'Another name' : 'Bad',
            ];
        }
    };

    try {
        $data::validate(['name' => '']);
    } catch (ValidationException $exception) {
        expect($exception->errors())->toMatchArray([
            "name" => [
                "The Another name field is required.",
            ],
        ]);

        return;
    }

    $this->fail('We should not end up here');
});

it('can resolve validation dependencies for redirect url', function () {
    $requestMock = mock(Request::class);
    $requestMock->expects('input')->andReturns('value');
    $this->app->bind(Request::class, fn() => $requestMock);

    $data = new class () extends Data {
        public string $name;

        public static function rules()
        {
            return [
                'name' => ['required'],
            ];
        }

        public static function redirect(Request $request): string
        {
            return $request->input('key') === 'value' ? 'Another name' : 'Bad';
        }
    };

    try {
        $data::validate(['name' => '']);
    } catch (ValidationException $exception) {
        expect($exception->redirectTo)->toBe('Another name');

        return;
    }

    $this->fail('We should not end up here');
});

it('can resolve validation dependencies for error bag', function () {
    $requestMock = mock(Request::class);
    $requestMock->expects('input')->andReturns('value');
    $this->app->bind(Request::class, fn() => $requestMock);

    $data = new class () extends Data {
        public string $name;

        public static function rules()
        {
            return [
                'name' => ['required'],
            ];
        }

        public static function errorBag(Request $request): string
        {
            return $request->input('key') === 'value' ? 'Another name' : 'Bad';
        }
    };

    try {
        $data::validate(['name' => '']);
    } catch (ValidationException $exception) {
        expect($exception->errorBag)->toBe('Another name');

        return;
    }

    $this->fail('We should not end up here');
});

it('can create data from a custom method with multiple parameters', function () {
    expect(DataWithMultipleArgumentCreationMethod::from('Rick Astley', 42))
        ->toEqual(new DataWithMultipleArgumentCreationMethod('Rick Astley_42'));
});

it('will validate a request when given as a parameter to a custom creation method', function () {
    $data = new class ('', 0) extends Data {
        public function __construct(
            public string $string,
        ) {
        }

        public static function fromRequest(Request $request)
        {
            return new self($request->input('string'));
        }
    };

    Route::post('/', fn(Request $request) => $data::from($request));

    postJson('/', [])->assertJsonValidationErrorFor('string');

    postJson('/', [
        'string' => 'Rick Astley',
    ])->assertJson([
        'string' => 'Rick Astley',
    ])->assertOk();
});

it('can resolve payload dependency for rules', function () {
    $data = new class () extends Data {
        public string $payment_method;

        public string|Optional $paypal_email;

        public static function rules(array $payload)
        {
            return [
                'payment_method' => ['required'],
                'paypal_email' => Rule::requiredIf($payload['payment_method'] === 'paypal'),
            ];
        }
    };

    $result = $data::validateAndCreate(['payment_method' => 'credit_card']);

    expect($result->toArray())->toMatchArray([
        'payment_method' => 'credit_card',
    ]);

    try {
        $data::validate(['payment_method' => 'paypal']);
    } catch (ValidationException $exception) {
        expect($exception->validator->failed())->toHaveKey('paypal_email');

        return;
    }

    $this->fail('We should not end up here');
});

it('can create data without custom creation methods', function () {
    $data = new class ('', '') extends Data {
        public function __construct(
            public ?string $id,
            public string $name,
        ) {
        }

        public static function fromArray(array $payload)
        {
            return new self(
                id: $payload['hash_id'] ?? null,
                name: $payload['name'],
            );
        }
    };

    expect(
        $data::withoutMagicalCreationFrom(['hash_id' => 1, 'name' => 'Taylor'])
    )->toEqual(new $data(null, 'Taylor'));

    expect($data::from(['hash_id' => 1, 'name' => 'Taylor']))
        ->toEqual(new $data(1, 'Taylor'));
});

it('can create data ignoring certain magical methods', function () {
    class DummyA extends Data
    {
        public function __construct(
            public ?string $id,
            public string $name,
        ) {
        }

        public static function fromArray(array $payload)
        {
            return new self(
                id: $payload['hash_id'] ?? null,
                name: $payload['name'],
            );
        }
    }

    expect(
        app(DataFromSomethingResolver::class)->ignoreMagicalMethods('fromArray')->execute(DummyA::class, ['hash_id' => 1, 'name' => 'Taylor'])
    )->toEqual(new DummyA(null, 'Taylor'));

    expect(
        app(DataFromSomethingResolver::class)->execute(DummyA::class, ['hash_id' => 1, 'name' => 'Taylor'])
    )->toEqual(new DummyA(1, 'Taylor'));
});
