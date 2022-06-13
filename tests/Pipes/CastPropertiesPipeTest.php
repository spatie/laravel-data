it('ma
uses(TestCase::class);
ps default types', function () {
    $data = ComplicatedData::from([
        'withoutType' => 42,
        'int' => 42,
        'bool' => true,
        'float' => 3.14,
        'string' => 'Hello world',
        'array' => [1, 1, 2, 3, 5, 8],
        'nullable' => null,
        'mixed' => 42,
        'explicitCast' => '16-06-1994',
        'defaultCast' => '1994-05-16T12:00:00+01:00',
        'nestedData' => [
            'string' => 'hello',
        ],
        'nestedCollection' => [
            ['string' => 'never'],
            ['string' => 'gonna'],
            ['string' => 'give'],
            ['string' => 'you'],
            ['string' => 'up'],
        ],
    ]);

    $this->assertInstanceOf(ComplicatedData::class, $data);
    $this->assertEquals(42, $data->withoutType);
    $this->assertEquals(42, $data->int);
    $this->assertTrue($data->bool);
    $this->assertEquals(3.14, $data->float);
    $this->assertEquals('Hello world', $data->string);
    $this->assertEquals([1, 1, 2, 3, 5, 8], $data->array);
    $this->assertNull($data->nullable);
    $this->assertInstanceOf(Optional::class, $data->undefinable);
    $this->assertEquals(42, $data->mixed);
    $this->assertEquals(DateTime::createFromFormat(DATE_ATOM, '1994-05-16T12:00:00+01:00'), $data->defaultCast);
    $this->assertEquals(CarbonImmutable::createFromFormat('d-m-Y', '16-06-1994'), $data->explicitCast);
    $this->assertEquals(SimpleData::from('hello'), $data->nestedData);
    $this->assertEquals(SimpleData::collection([
        SimpleData::from('never'),
        SimpleData::from('gonna'),
        SimpleData::from('give'),
        SimpleData::from('you'),
        SimpleData::from('up'),
    ]), $data->nestedCollection);
});

it('wont cast a property that is already in the correct type', function () {
    $data = ComplicatedData::from([
        'withoutType' => 42,
        'int' => 42,
        'bool' => true,
        'float' => 3.14,
        'string' => 'Hello world',
        'array' => [1, 1, 2, 3, 5, 8],
        'nullable' => null,
        'mixed' => 42,
        'explicitCast' => DateTime::createFromFormat('d-m-Y', '16-06-1994'),
        'defaultCast' => DateTime::createFromFormat(DATE_ATOM, '1994-05-16T12:00:00+02:00'),
        'nestedData' => SimpleData::from('hello'),
        'nestedCollection' => SimpleData::collection([
            'never', 'gonna', 'give', 'you', 'up',
        ]),
    ]);

    $this->assertInstanceOf(ComplicatedData::class, $data);
    $this->assertEquals(42, $data->withoutType);
    $this->assertEquals(42, $data->int);
    $this->assertTrue($data->bool);
    $this->assertEquals(3.14, $data->float);
    $this->assertEquals('Hello world', $data->string);
    $this->assertEquals([1, 1, 2, 3, 5, 8], $data->array);
    $this->assertNull($data->nullable);
    $this->assertEquals(42, $data->mixed);
    $this->assertEquals(DateTime::createFromFormat(DATE_ATOM, '1994-05-16T12:00:00+02:00'), $data->defaultCast);
    $this->assertEquals(DateTime::createFromFormat('d-m-Y', '16-06-1994'), $data->explicitCast);
    $this->assertEquals(SimpleData::from('hello'), $data->nestedData);
    $this->assertEquals(SimpleData::collection([
        SimpleData::from('never'),
        SimpleData::from('gonna'),
        SimpleData::from('give'),
        SimpleData::from('you'),
        SimpleData::from('up'),
    ]), $data->nestedCollection);
});

it('will allow a nested data object to handle their own types', function () {
    $model = new DummyModel(['id' => 10]);

    $withoutModelData = NestedModelData::from([
        'model' => ['id' => 10],
    ]);

    $this->assertInstanceOf(NestedModelData::class, $withoutModelData);
    $this->assertEquals(10, $withoutModelData->model->id);

    /** @var \Spatie\LaravelData\Tests\Fakes\NestedModelData $data */
    $withModelData = NestedModelData::from([
        'model' => $model,
    ]);

    $this->assertInstanceOf(NestedModelData::class, $withModelData);
    $this->assertEquals(10, $withModelData->model->id);
});

it('will allow a nested collection object to handle its own types', function () {
    $data = NestedModelCollectionData::from([
        'models' => [['id' => 10], ['id' => 20],],
    ]);

    $this->assertInstanceOf(NestedModelCollectionData::class, $data);
    $this->assertEquals(
        ModelData::collection([['id' => 10], ['id' => 20]]),
        $data->models
    );

    $data = NestedModelCollectionData::from([
        'models' => [new DummyModel(['id' => 10]), new DummyModel(['id' => 20]),],
    ]);

    $this->assertInstanceOf(NestedModelCollectionData::class, $data);
    $this->assertEquals(
        ModelData::collection([['id' => 10], ['id' => 20]]),
        $data->models
    );

    $data = NestedModelCollectionData::from([
        'models' => ModelData::collection([['id' => 10], ['id' => 20]]),
    ]);

    $this->assertInstanceOf(NestedModelCollectionData::class, $data);
    $this->assertEquals(
        ModelData::collection([['id' => 10], ['id' => 20]]),
        $data->models
    );
});

it('works nicely with lazy data', function () {
    $data = NestedLazyData::from([
        'simple' => Lazy::create(fn () => SimpleData::from('Hello')),
    ]);

    $this->assertInstanceOf(Lazy::class, $data->simple);
    $this->assertEquals(Lazy::create(fn () => SimpleData::from('Hello')), $data->simple);
});

it('allows casting of built in types', function () {
    $data = BuiltInTypeWithCastData::from([
        'money' => 3.14,
    ]);

    $this->assertIsInt($data->money);
    $this->assertEquals(314, $data->money);
});

it('allows casting', function () {
    $data = DateCastData::from([
        'date' => '2022-01-18',
    ]);

    $this->assertInstanceOf(DateTimeImmutable::class, $data->date);
    $this->assertEquals(DateTimeImmutable::createFromFormat('Y-m-d', '2022-01-18'), $data->date);
});

it('allows casting of enums', function () {
    $this->onlyPHP81();

    $data = EnumData::from([
        'enum' => 'foo',
    ]);

    $this->assertInstanceOf(DummyBackedEnum::class, $data->enum);
    $this->assertEquals(DummyBackedEnum::FOO, $data->enum);
});

it('can manually set values in the constructor', function () {
    $dataClass = new class ('', '') extends Data {
        public string $member;

        public string $other_member;

        public string $member_with_default = 'default';

        public string $member_to_set;

        public function __construct(
            public string $promoted,
            string $non_promoted,
            string $non_promoted_with_default = 'default',
            public string $promoted_with_with_default = 'default',
        ) {
            $this->member = "changed_in_constructor: {$non_promoted}";
            $this->other_member = "changed_in_constructor: {$non_promoted_with_default}";
        }
    };

    $data = $dataClass::from([
        'promoted' => 'A',
        'non_promoted' => 'B',
        'non_promoted_with_default' => 'C',
        'promoted_with_with_default' => 'D',
        'member_to_set' => 'E',
        'member_with_default' => 'F',
    ]);

    $this->assertEquals([
        'member' => 'changed_in_constructor: B',
        'other_member' => 'changed_in_constructor: C',
        'member_with_default' => 'F',
        'promoted' => 'A',
        'promoted_with_with_default' => 'D',
        'member_to_set' => 'E',
    ], $data->toArray());

    $data = $dataClass::from([
        'promoted' => 'A',
        'non_promoted' => 'B',
        'member_to_set' => 'E',
    ]);

    $this->assertEquals([
        'member' => 'changed_in_constructor: B',
        'other_member' => 'changed_in_constructor: default',
        'member_with_default' => 'default',
        'promoted' => 'A',
        'promoted_with_with_default' => 'default',
        'member_to_set' => 'E',
    ], $data->toArray());
});
