
// Da
uses(TestCase::class);
tasets
dataset('acceptedBaseTypes', function () {
    yield [
        'class' => new class () {
            public $property;
        },
        'expected' => [],
    ];

    yield [
        'class' => new class () {
            public mixed $property;
        },
        'expected' => [],
    ];

    yield [
        'class' => new class () {
            public string $property;
        },
        'expected' => ['string' => []],
    ];

    yield [
        'class' => new class () {
            public string|int|bool|float|array $property;
        },
        'expected' => [
            'string' => [],
            'int' => [],
            'bool' => [],
            'float' => [],
            'array' => [],
        ],
    ];

    yield [
        'class' => new class () {
            public SimpleData $property;
        },
        'expected' => [
            SimpleData::class => [
                Data::class,
                JsonSerializable::class,
                Castable::class,
                Jsonable::class,
                Responsable::class,
                Arrayable::class,
                DataObject::class,
                AppendableData::class,
                BaseData::class,
                IncludeableData::class,
                ResponsableData::class,
                TransformableData::class,
                ValidateableData::class,
                WrappableData::class,
            ],
        ],
    ];

    yield [
        'class' => new class () {
            public DummyBackedEnum $property;
        },
        'expected' => [
            DummyBackedEnum::class => [
                UnitEnum::class,
                BackedEnum::class,
            ],
        ],
    ];
});

dataset('acceptType', function () {
    // Base types

    yield [
        'class' => new class () {
            public $property;
        },
        'type' => 'string',
        'accepts' => true,
    ];

    yield [
        'class' => new class () {
            public mixed $property;
        },
        'type' => 'string',
        'accepts' => true,
    ];

    yield [
        'class' => new class () {
            public string $property;
        },
        'type' => 'string',
        'accepts' => true,
    ];

    yield [
        'class' => new class () {
            public bool $property;
        },
        'type' => 'bool',
        'accepts' => true,
    ];

    yield [
        'class' => new class () {
            public int $property;
        },
        'type' => 'int',
        'accepts' => true,
    ];

    yield [
        'class' => new class () {
            public float $property;
        },
        'type' => 'float',
        'accepts' => true,
    ];

    yield [
        'class' => new class () {
            public array $property;
        },
        'type' => 'array',
        'accepts' => true,
    ];

    yield [
        'class' => new class () {
            public string $property;
        },
        'type' => 'array',
        'accepts' => false,
    ];

    // Objects

    yield [
        'class' => new class () {
            public SimpleData $property;
        },
        'type' => SimpleData::class,
        'accepts' => true,
    ];

    yield [
        'class' => new class () {
            public SimpleData $property;
        },
        'type' => ComplicatedData::class,
        'accepts' => false,
    ];

    // Objects with inheritance

    yield 'simple inheritance' => [
        'class' => new class () {
            public Data $property;
        },
        'type' => SimpleData::class,
        'accepts' => true,
    ];

    yield 'reversed inheritance' => [
        'class' => new class () {
            public SimpleData $property;
        },
        'type' => Data::class,
        'accepts' => false,
    ];

    yield 'false inheritance' => [
        'class' => new class () {
            public Model $property;
        },
        'type' => SimpleData::class,
        'accepts' => false,
    ];

    // Objects with interfaces

    yield 'simple interface implementation' => [
        'class' => new class () {
            public DateTimeInterface $property;
        },
        'type' => DateTime::class,
        'accepts' => true,
    ];

    yield 'reversed interface implementation' => [
        'class' => new class () {
            public DateTime $property;
        },
        'type' => DateTimeInterface::class,
        'accepts' => false,
    ];

    yield 'false interface implementation' => [
        'class' => new class () {
            public Model $property;
        },
        'type' => DateTime::class,
        'accepts' => false,
    ];

    // Enums

    yield [
        'class' => new class () {
            public DummyBackedEnum $property;
        },
        'type' => DummyBackedEnum::class,
        'accepts' => true,
    ];
});

dataset('acceptValue', function () {
    yield [
        'class' => new class () {
            public ?string $property;
        },
        'value' => null,
        'accepts' => true,
    ];

    yield [
        'class' => new class () {
            public string $property;
        },
        'value' => 'Hello',
        'accepts' => true,
    ];

    yield [
        'class' => new class () {
            public string $property;
        },
        'value' => 3.14,
        'accepts' => false,
    ];

    yield [
        'class' => new class () {
            public mixed $property;
        },
        'value' => 3.14,
        'accepts' => true,
    ];

    yield [
        'class' => new class () {
            public Data $property;
        },
        'value' => new SimpleData('Hello'),
        'accepts' => true,
    ];

    yield [
        'class' => new class () {
            public SimpleData $property;
        },
        'value' => new SimpleData('Hello'),
        'accepts' => true,
    ];

    yield [
        'class' => new class () {
            public SimpleData $property;
        },
        'value' => new SimpleDataWithMappedProperty('Hello'),
        'accepts' => false,
    ];

    yield [
        'class' => new class () {
            public DummyBackedEnum $property;
        },
        'value' => DummyBackedEnum::FOO,
        'accepts' => true,
    ];
});

dataset('acceptedTypeForBaseTypes', function () {
    yield [
        'class' => new class () {
            public SimpleData $property;
        },
        'type' => SimpleData::class,
        'expectedType' => SimpleData::class,
    ];

    yield [
        'class' => new class () {
            public SimpleData $property;
        },
        'type' => Data::class,
        'expectedType' => SimpleData::class,
    ];

    yield [
        'class' => new class () {
            public DummyBackedEnum $property;
        },
        'type' => BackedEnum::class,
        'expectedType' => DummyBackedEnum::class,
    ];

    yield [
        'class' => new class () {
            public SimpleData $property;
        },
        'type' => DataCollection::class,
        'expectedType' => null,
    ];
});

dataset('correctAnnotations', function () {
    yield [
        'property' => 'propertyA',
        'expected' => SimpleData::class,
    ];

    yield [
        'property' => 'propertyB',
        'expected' => SimpleData::class,
    ];

    yield [
        'property' => 'propertyC',
        'expected' => SimpleData::class,
    ];

    yield [
        'property' => 'propertyD',
        'expected' => SimpleData::class,
    ];

    yield [
        'property' => 'propertyE',
        'expected' => SimpleData::class,
    ];

    yield [
        'property' => 'propertyF',
        'expected' => SimpleData::class,
    ];

    yield [
        'property' => 'propertyG',
        'expected' => SimpleData::class,
    ];

    yield [
        'property' => 'propertyH',
        'expected' => SimpleData::class,
    ];

    yield [
        'property' => 'propertyI',
        'expected' => SimpleData::class,
    ];

    yield [
        'property' => 'propertyJ',
        'expected' => SimpleData::class,
    ];

    yield [
        'property' => 'propertyK',
        'expected' => SimpleData::class,
    ];

    yield [
        'property' => 'propertyL',
        'expected' => SimpleData::class,
    ];
});

dataset('invalidAnnotations', function () {
    yield [
        'property' => 'propertyM',
    ];

    yield [
        'property' => 'propertyN',
    ];

    yield [
        'property' => 'propertyO',
    ];
});

// Helpers
function resolveDataType(object $class, string $property = 'property'): DataType
{
    return DataType::create(new ReflectionProperty($class, $property));
}
