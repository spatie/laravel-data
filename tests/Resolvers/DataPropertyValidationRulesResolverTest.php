it('wi
uses(TestCase::class);
ll add a required or nullable rule based upon the property nullability', function () {
    $rules = resolveRules(new class () {
        public int $property;
    });

    $this->assertEquals([
        'property' => ['numeric', 'required'],
    ], $rules);

    $rules = resolveRules(new class () {
        public ?int $property;
    });

    $this->assertEquals([
        'property' => ['numeric', 'nullable'],
    ], $rules);
});

it('will add basic rules for certain types', function () {
    $rules = resolveRules(new class () {
        public string $property;
    });

    $this->assertEquals([
        'property' => ['string', 'required'],
    ], $rules);

    $rules = resolveRules(new class () {
        public int $property;
    });

    $this->assertEquals([
        'property' => ['numeric', 'required'],
    ], $rules);

    $rules = resolveRules(new class () {
        public bool $property;
    });

    $this->assertEquals([
        'property' => ['boolean'],
    ], $rules);

    $rules = resolveRules(new class () {
        public float $property;
    });

    $this->assertEquals([
        'property' => ['numeric', 'required'],
    ], $rules);

    $rules = resolveRules(new class () {
        public array $property;
    });

    $this->assertEquals([
        'property' => ['array', 'required'],
    ], $rules);
});

it('will add rules for enums', function () {
    $this->onlyPHP81();

    $rules = resolveRules(new class () {
        public FakeEnum $property;
    });

    $this->assertEquals([
        'property' => [new EnumRule(FakeEnum::class), 'required'],
    ], $rules);
});

it('will take validation attributes into account', function () {
    $rules = resolveRules(new class () {
        #[Max(10)]
        public string $property;
    });

    $this->assertEquals([
        'property' => ['string', 'max:10', 'required'],
    ], $rules);
});

it('will take rules from nested data objects', function () {
    $rules = resolveRules(new class () {
        public SimpleData $property;
    });

    $this->assertEquals([
        'property' => ['required', 'array'],
        'property.string' => ['string', 'required'],
    ], $rules);

    $rules = resolveRules(new class () {
        public ?SimpleData $property;
    });

    $this->assertEquals([
        'property' => ['nullable', 'array'],
        'property.string' => ['nullable', 'string'],
    ], $rules);
});

it('will take rules from nested data collections', function () {
    $rules = resolveRules(new class () {
        /** @var \Spatie\LaravelData\Tests\Fakes\SimpleData[] */
        public DataCollection $property;
    });

    $this->assertEquals([
        'property' => ['present', 'array'],
        'property.*.string' => ['string', 'required'],
    ], $rules);

    $rules = resolveRules(new class () {
        /** @var \Spatie\LaravelData\Tests\Fakes\SimpleData[]|null */
        public ?DataCollection $property;
    });

    $this->assertEquals([
        'property' => ['nullable', 'array'],
        'property.*.string' => ['string', 'required'],
    ], $rules);
});

it('can nest validation rules event further', function () {
    $rules = resolveRules(new class () {
        public NestedData $property;
    });

    $this->assertEquals([
        'property' => ['required', 'array'],
        'property.simple' => ['required', 'array'],
        'property.simple.string' => ['string', 'required'],
    ], $rules);

    $rules = resolveRules(new class () {
        public ?SimpleData $property;
    });

    $this->assertEquals([
        'property' => ['nullable', 'array'],
        'property.string' => ['nullable', 'string'],
    ], $rules);
});

it('will never add extra require rules when not needed', function () {
//        $rules = resolveRules(new class () {
//            public ?string $property;
//        });
//
//        $this->assertEquals([
//            'property' => ['string', new Nullable()],
//        ], $rules);
//
//        $rules = resolveRules(new class () {
//            public bool $property;
//        });
//
//        $this->assertEquals([
//            'property' => ['boolean'],
//        ], $rules);
//
//        $rules = resolveRules(new class () {
//            #[RequiredWith('other')]
//            public string $property;
//        });
//
//        $this->assertEquals([
//            'property' => ['string', 'required_with:other'],
//        ], $rules);

    $rules = resolveRules(new class () {
        #[Rule('required_with:other')]
        public string $property;
    });

    $this->assertEquals([
        'property' => ['string', 'required_with:other'],
    ], $rules);
});

it('will work with non string rules', function () {
    $rules = resolveRules(new class () {
        #[Enum(FakeEnum::class)]
        public string $property;
    });

    $this->assertEquals([
        'property' => ['string', new EnumRule(FakeEnum::class), 'required'],
    ], $rules);
});

it('will take mapped properties into account', function () {
    $rules = resolveRules(new class () {
        #[MapName('other')]
        public int $property;
    });

    $this->assertEquals([
        'other' => ['numeric', 'required'],
    ], $rules);

    $rules = resolveRules(new class () {
        #[MapName('other')]
        public SimpleData $property;
    });

    $this->assertEquals([
        'other' => ['required', 'array'],
        'other.string' => ['string', 'required'],
    ], $rules);

    $rules = resolveRules(new class () {
        #[DataCollectionOf(SimpleData::class), MapName('other')]
        public DataCollection $property;
    });

    $this->assertEquals([
        'other' => ['present', 'array'],
        'other.*.string' => ['string', 'required'],
    ], $rules);


    $rules = resolveRules(new class () {
        #[MapName('other')]
        public DataWithMapper $property;
    });

    $this->assertEquals([
        'other' => ['required', 'array'],
        'other.cased_property' => ['string', 'required'],
        'other.data_cased_property' => ['required', 'array'],
        'other.data_cased_property.string' => ['string', 'required'],
        'other.data_collection_cased_property' => ['present', 'array'],
        'other.data_collection_cased_property.*.string' => ['string', 'required'],
    ], $rules);
});

// Helpers
function resolveRules(object $class): array
{
    $reflectionProperty = new ReflectionProperty($class, 'property');

    $property = DataProperty::create($reflectionProperty);

    return app(DataPropertyValidationRulesResolver::class)->execute($property)->toArray();
}
