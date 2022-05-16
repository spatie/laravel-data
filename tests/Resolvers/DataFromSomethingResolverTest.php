<?php

namespace Spatie\LaravelData\Tests\Resolvers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Tests\Fakes\DummyDto;
use Spatie\LaravelData\Tests\Fakes\DummyModel;
use Spatie\LaravelData\Tests\Fakes\DummyModelWithCasts;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\LaravelData\Optional;

class DataFromSomethingResolverTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->handleExceptions([
            ValidationException::class,
        ]);
    }

    /** @test */
    public function it_can_create_data_from_a_custom_method()
    {
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

        $this->assertEquals(new $data('Hello World'), $data::from('Hello World'));
        $this->assertEquals(new $data('Rick Astley'), $data::from(DummyDto::rick()));
        $this->assertEquals(new $data('Hello World'), $data::from(['string' => 'Hello World']));
        $this->assertEquals(new $data('Hello World'), $data::from(DummyModelWithCasts::make(['string' => 'Hello World'])));
    }

    /** @test */
    public function it_can_create_data_from_a_custom_method_with_an_interface_parameter()
    {
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

        $this->assertEquals(new $data('Rick Astley'), $data::from($interfaceable));
    }

    /** @test */
    public function it_can_create_data_from_a_custom_method_with_an_inherited_parameter()
    {
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

        $this->assertEquals(new $data('Rick Astley'), $data::from($inherited));
    }

    /** @test */
    public function it_can_resolve_validation_dependencies_for_messages()
    {
        $requestMock = $this->mock(Request::class);
        $requestMock->expects('input')->andReturns('value');
        $this->app->bind(Request::class, fn () => $requestMock);

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
            $this->assertEquals([
                "name" => [
                    "Name is required",
                ],
            ], $exception->errors());

            return;
        }

        $this->fail('We should not end up here');
    }

    /** @test */
    public function it_can_resolve_validation_dependencies_for_attributes()
    {
        $requestMock = $this->mock(Request::class);
        $requestMock->expects('input')->andReturns('value');
        $this->app->bind(Request::class, fn () => $requestMock);

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
            $this->assertEquals([
                "name" => [
                    "The Another name field is required.",
                ],
            ], $exception->errors());

            return;
        }

        $this->fail('We should not end up here');
    }

    /** @test */
    public function it_can_resolve_validation_dependencies_for_redirect_url()
    {
        $requestMock = $this->mock(Request::class);
        $requestMock->expects('input')->andReturns('value');
        $this->app->bind(Request::class, fn () => $requestMock);

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
            $this->assertEquals('Another name', $exception->redirectTo);

            return;
        }

        $this->fail('We should not end up here');
    }

    /** @test */
    public function it_can_resolve_validation_dependencies_for_error_bag()
    {
        $requestMock = $this->mock(Request::class);
        $requestMock->expects('input')->andReturns('value');
        $this->app->bind(Request::class, fn () => $requestMock);

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
            $this->assertEquals('Another name', $exception->errorBag);

            return;
        }

        $this->fail('We should not end up here');
    }

    /** @test */
    public function it_can_create_data_from_a_custom_method_with_multiple_parameters()
    {
        $data = new class ('', 0) extends Data {
            public function __construct(
                public string $string,
                public int $number,
            ) {
            }

            public static function fromMultiple(string $first, int $second)
            {
                return new self($first, $second);
            }
        };

        $this->assertEquals(new $data('Rick Astley', 42), $data::from(
            'Rick Astley',
            42,
        ));
    }

    /** @test */
    public function it_will_validate_a_request_when_given_as_a_parameter_to_a_custom_creation_method()
    {
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

        Route::post('/', fn (Request $request) => $data::from($request));

        $this->postJson('/', [])->assertJsonValidationErrorFor('string');

        $this->postJson('/', [
            'string' => 'Rick Astley',
        ])->assertJson([
            'string' => 'Rick Astley',
        ])->assertOk();
    }

    /** @test */
    public function it_can_resolve_payload_dependency_for_rules()
    {
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

        $this->assertEquals([
            'payment_method' => 'credit_card',
        ], $result->toArray());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The paypal email field is required');

        $data::validate(['payment_method' => 'paypal']);
    }
}
