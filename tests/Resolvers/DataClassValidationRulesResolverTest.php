it('wi
ll resolve rules for a data object', function () {
    $data = new class () extends Data {
        public string $name;

        public ?int $age;
    };

    $this->assertEquals([
        'name' => ['string', 'required'],
        'age' => ['numeric', 'nullable'],
    ], $this->resolver->execute($data::class)->all());
});

it('will make properties nullable if required', function () {
    $data = new class () extends Data {
        public string $name;

        public ?int $age;
    };

    $this->assertEquals([
        'name' => [new Nullable(), new StringType()],
        'age' => [new Numeric(),  new Nullable()],
    ], $this->resolver->execute($data::class, nullable: true)->all());
});

it('will merge overwritten rules on the data object', function () {
    $data = new class () extends Data {
        public string $name;

        public static function rules(): array
        {
            return [
                'name' => ['string', 'required', 'min:10', 'max:100'],
            ];
        }
    };

    $this->assertEqualsCanonicalizing([
        'name' => ['string', 'required', 'min:10', 'max:100'],
    ], $this->resolver->execute($data::class)->all());
});

it('will merge overwritten rules on nested data objects', function () {
    $data = new class () extends Data {
        public SimpleDataWithOverwrittenRules $nested;

        /** @var DataCollection<\Spatie\LaravelData\Tests\Fakes\SimpleDataWithOverwrittenRules> */
        public DataCollection $collection;
    };

    $this->assertEqualsCanonicalizing([
        'nested' => ['array', 'required'],
        'nested.string' => ['string', 'required', 'min:10', 'max:100'],
        'collection' => ['array', 'present'],
        'collection.*.string' => ['string', 'required', 'min:10', 'max:100'],
    ], $this->resolver->execute($data::class)->all());
});

it('can skip certain properties from being validated', function () {
    $data = new class () extends Data {
        #[WithoutValidation]
        public string $skip_string;

        #[WithoutValidation]
        public SimpleData $skip_data;

        #[WithoutValidation, DataCollectionOf(SimpleData::class)]
        public DataCollection $skip_data_collection;

        public ?int $age;
    };

    $this->assertEquals([
        'age' => ['numeric', 'nullable'],
    ], $this->resolver->execute($data::class)->all());
});

it('can resolve dependencies when calling rules', function () {
    $requestMock = $this->mock(Request::class);
    $requestMock->expects('input')->andReturns('value');
    app()->bind(Request::class, fn () => $requestMock);

    $data = new class () extends Data {
        public string $name;

        public static function rules(Request $request): array
        {
            return [
                'name' => $request->input('key') === 'value' ? ['required'] : ['bail'],
            ];
        }
    };

    $this->assertEquals([
        'name' => ['required'],
    ], $this->resolver->execute($data::class)->all());
});

it('can resolve payload when calling rules', function () {
    $data = new class () extends Data {
        public string $name;

        public static function rules(array $payload): array
        {
            return [
                'name' => $payload['name'] === 'foo' ? ['required'] : ['sometimes'],
            ];
        }
    };

    $this->assertEquals([
        'name' => ['required'],
    ], $this->resolver->execute($data::class, ['name' => 'foo'])->all());
});
