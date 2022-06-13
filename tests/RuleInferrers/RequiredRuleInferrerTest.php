it('wo
nt add a required rule when a property is non nullable', function () {
    $dataProperty = getProperty(new class () extends Data {
        public string $string;
    });

    $rules = $this->inferrer->handle($dataProperty, new RulesCollection());

    $this->assertEqualsCanonicalizing([new Required()], $rules->all());
});

it('wont add a required rule when a property is nullable', function () {
    $dataProperty = getProperty(new class () extends Data {
        public ?string $string;
    });

    $rules = $this->inferrer->handle($dataProperty, new RulesCollection());

    $this->assertEqualsCanonicalizing([], $rules->all());
});

it('wont add a required rule when a property already contains a required rule', function () {
    $dataProperty = getProperty(new class () extends Data {
        public string $string;
    });

    $rules = $this->inferrer->handle($dataProperty, RulesCollection::create()->add(new RequiredIf('bla')));

    $this->assertEqualsCanonicalizing(['required_if:bla'], $rules->all());
});

it('wont add a required rule when a property already contains a required object rule', function () {
    $dataProperty = getProperty(new class () extends Data {
        public string $string;
    });

    $rules = $this->inferrer->handle(
        $dataProperty,
        RulesCollection::create()->add(Required::create())
    );

    $this->assertEqualsCanonicalizing(['required'], $rules->normalize());
});

it('wont add a required rule when a property already contains a boolean rule', function () {
    $dataProperty = getProperty(new class () extends Data {
        public string $string;
    });

    $rules = $this->inferrer->handle(
        $dataProperty,
        RulesCollection::create()->add(BooleanType::create())
    );

    $this->assertEqualsCanonicalizing([new BooleanType()], $rules->normalize());
});

it('wont add a required rule when a property already contains a nullable rule', function () {
    $dataProperty = getProperty(new class () extends Data {
        public string $string;
    });

    $rules = $this->inferrer->handle(
        $dataProperty,
        RulesCollection::create()->add(Nullable::create())
    );

    $this->assertEqualsCanonicalizing([new Nullable()], $rules->normalize());
});

it('has support for rules that cannot be converted to string', function () {
    $dataProperty = getProperty(new class () extends Data {
        public string $string;
    });

    $rules = $this->inferrer->handle(
        $dataProperty,
        RulesCollection::create()->add(new \Spatie\LaravelData\Attributes\Validation\Enum(new BaseEnum('SomeClass')))
    );

    $this->assertEqualsCanonicalizing(['required', new BaseEnum('SomeClass')], $rules->normalize());
});

it('wont add required to a data collection since it is already present', function () {
    $dataProperty = getProperty(new class () extends Data {
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection $collection;
    });

    $rules = $this->inferrer->handle(
        $dataProperty,
        RulesCollection::create()->add(new Present(), new ArrayType())
    );

    $this->assertEqualsCanonicalizing(['present', 'array'], $rules->normalize());
});

// Helpers
function it_wont_add_required_rules_to_undefinable_properties()
{
    $dataProperty = test()->getProperty(new class () extends Data {
        public string|Optional $string;
    });

    $rules = test()->inferrer->handle($dataProperty, []);

    test()->assertEqualsCanonicalizing([], $rules);
}

function getProperty(object $class): DataProperty
{
    $dataClass = DataClass::create(new ReflectionClass($class));

    return $dataClass->properties->first();
}
