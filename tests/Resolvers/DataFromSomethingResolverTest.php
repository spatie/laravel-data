it('ca
n create data from a custom method', function () {
    $data = new class ('') extends Data {
        public function __construct(public string $string) {
        }

        public static function fromString(string $string): static
        {
            return new self($string);
        }

        public static function fromDto(DummyDto $dto) {
            return new self($dto->artist);
        }

        public static function fromArray(array $payload) {
            return new self($payload['string']);
        }
    };

    $this->assertEquals(new $data('Hello World'), $data::from('Hello World'));
    $this->assertEquals(new $data('Rick Astley'), $data::from(DummyDto::rick()));
    $this->assertEquals(new $data('Hello World'), $data::from(['string' => 'Hello World']));
    $this->assertEquals(new $data('Hello World'), $data::from(DummyModelWithCasts::make(['string' => 'Hello World'])));
});

it('can create data from a custom method with an interface parameter', function () {
    $data = new class ('') extends Data {
        public function __construct(public string $string) {
        }

        public static function fromInterface(Arrayable $arrayable) {
            return new self($arrayable->toArray()['string']);
        }
    };

    $interfaceable = new class () implements Arrayable {
        public function toArray() {
            return [
                'string' => 'Rick Astley',
            ];
        }
    };

    $this->assertEquals(new $data('Rick Astley'), $data::from($interfaceable));
});

it('can create data from a custom method with an inherited parameter', function () {
    $data = new class ('') extends Data {
        public function __construct(public string $string) {
        }

        public static function fromModel(Model $model) {
            return new self($model->string);
        }
    };

    $inherited = new DummyModel(['string' => 'Rick Astley']);

    $this->assertEquals(new $data('Rick Astley'), $data::from($inherited));
});

it('can resolve validation dependencies for messages', function () {
    $requestMock = $this->mock(Request::class);
    $requestMock->expects('input')->andReturns('value');
    app()->bind(Request::class, fn () => $requestMock);

    $data = new class () extends Data {
        public string $name;

        public static function rules() {
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
});

it('can resolve validation dependencies for attributes', function () {
    $requestMock = $this->mock(Request::class);
    $requestMock->expects('input')->andReturns('value');
    app()->bind(Request::class, fn () => $requestMock);

    $data = new class () extends Data {
        public string $name;

        public static function rules() {
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
});

it('can resolve validation dependencies for redirect url', function () {
    $requestMock = $this->mock(Request::class);
    $requestMock->expects('input')->andReturns('value');
    app()->bind(Request::class, fn () => $requestMock);

    $data = new class () extends Data {
        public string $name;

        public static function rules() {
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
});

it('can resolve validation dependencies for error bag', function () {
    $requestMock = $this->mock(Request::class);
    $requestMock->expects('input')->andReturns('value');
    app()->bind(Request::class, fn () => $requestMock);

    $data = new class () extends Data {
        public string $name;

        public static function rules() {
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
});

it('can create data from a custom method with multiple parameters', function () {
    $this->assertEquals(
        new DataWithMultipleArgumentCreationMethod('Rick Astley_42'),
        DataWithMultipleArgumentCreationMethod::from('Rick Astley', 42)
    );
});

it('will validate a request when given as a parameter to a custom creation method', function () {
    $data = new class ('', 0) extends Data {
        public function __construct(
            public string $string,
        ) {
        }

        public static function fromRequest(Request $request) {
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
});

it('can resolve payload dependency for rules', function () {
    $data = new class () extends Data {
        public string $payment_method;

        public string|Optional $paypal_email;

        public static function rules(array $payload) {
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

    try {
        $data::validate(['payment_method' => 'paypal']);
    } catch (ValidationException $exception) {
        $this->assertArrayHasKey('paypal_email', $exception->validator->failed());

        return;
    }

    $this->fail('We should not end up here');
});
