<?php

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Contracts\AppendableData;
use Spatie\LaravelData\Contracts\BaseData;
use Spatie\LaravelData\Contracts\DataObject;
use Spatie\LaravelData\Contracts\EmptyData;
use Spatie\LaravelData\Contracts\IncludeableData;
use Spatie\LaravelData\Contracts\PrepareableData;
use Spatie\LaravelData\Contracts\ResponsableData;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Contracts\ValidateableData;
use Spatie\LaravelData\Contracts\WrappableData;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Enums\DataCollectableType;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Exceptions\CannotFindDataClass;
use Spatie\LaravelData\Exceptions\InvalidDataType;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\Annotations\DataCollectableAnnotationReader;
use Spatie\LaravelData\Support\DataType;
use Spatie\LaravelData\Support\Factories\DataTypeFactory;
use Spatie\LaravelData\Tests\Fakes\CollectionAnnotationsData;
use Spatie\LaravelData\Tests\Fakes\ComplicatedData;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedProperty;

function resolveDataType(object $class, string $property = 'property'): DataType
{
    $reflectionProperty = new ReflectionProperty($class, $property);

    return DataTypeFactory::create()->build(
        $reflectionProperty,
        DataCollectableAnnotationReader::create()->getForClass($reflectionProperty->getDeclaringClass())[$property] ?? null,
    );
}

it('can deduce a type without definition', function () {
    $type = resolveDataType(new class () {
        public $property;
    });

    expect($type)
        ->isNullable->toBeTrue()
        ->isMixed->toBeTrue()
        ->isLazy->toBeFalse()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull()
        ->acceptedTypes->toBeEmpty();
});

it('can deduce a type with definition', function () {
    $type = resolveDataType(new class () {
        public string $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeFalse()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull()
        ->acceptedTypes->toHaveKeys(['string']);
});

it('can deduce a nullable type with definition', function () {
    $type = resolveDataType(new class () {
        public ?string $property;
    });

    expect($type)
        ->isNullable->toBeTrue()
        ->isMixed->toBeFalse()
        ->isLazy->toBeFalse()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectionClass->toBeNull()
        ->acceptedTypes->toHaveKeys(['string']);
});

it('can deduce a union type definition', function () {
    $type = resolveDataType(new class () {
        public string|int $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeFalse()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull()
        ->acceptedTypes->toHaveKeys(['string', 'int']);
});

it('can deduce a nullable union type definition', function () {
    $type = resolveDataType(new class () {
        public string|int|null $property;
    });

    expect($type)
        ->isNullable->toBeTrue()
        ->isMixed->toBeFalse()
        ->isLazy->toBeFalse()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull()
        ->acceptedTypes->toHaveKeys(['string', 'int']);
});

it('can deduce an intersection type definition', function () {
    $type = resolveDataType(new class () {
        public DateTime & DateTimeImmutable $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeFalse()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull()
        ->acceptedTypes->toHaveKeys([
            DateTime::class,
            DateTimeImmutable::class,
        ]);
});

it('can deduce a mixed type', function () {
    $type = resolveDataType(new class () {
        public mixed $property;
    });

    expect($type)
        ->isNullable->toBeTrue()
        ->isMixed->toBeTrue()
        ->isLazy->toBeFalse()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull()
        ->acceptedTypes->toHaveKeys([]);
});

it('can deduce a lazy type', function () {
    $type = resolveDataType(new class () {
        public string|Lazy $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeTrue()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull()
        ->acceptedTypes->toHaveKeys(['string']);
});

it('can deduce an optional type', function () {
    $type = resolveDataType(new class () {
        public string|Optional $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeFalse()
        ->isOptional->toBeTrue()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull()
        ->acceptedTypes->toHaveKeys(['string']);
});

test('a type cannot be optional alone', function () {
    resolveDataType(new class () {
        public Optional $property;
    });
})->throws(InvalidDataType::class);

it('can deduce a data type', function () {
    $type = resolveDataType(new class () {
        public SimpleData $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeFalse()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::DataObject)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBeNull()
        ->acceptedTypes->toHaveKeys([SimpleData::class]);
});

it('can deduce a data union type', function () {
    $type = resolveDataType(new class () {
        public SimpleData|Lazy $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeTrue()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::DataObject)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBeNull()
        ->acceptedTypes->toHaveKeys([SimpleData::class]);
});

it('can deduce a data collection type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeFalse()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::DataCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(DataCollection::class)
        ->acceptedTypes->toHaveKeys([DataCollection::class]);
});

it('can deduce a data collection union type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection|Lazy $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeTrue()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::DataCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(DataCollection::class)
        ->acceptedTypes->toHaveKeys([DataCollection::class]);
});

it('can deduce a paginated data collection type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public PaginatedDataCollection $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeFalse()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::DataPaginatedCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(PaginatedDataCollection::class)
        ->acceptedTypes->toHaveKeys([PaginatedDataCollection::class]);
});

it('can deduce a paginated data collection union type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public PaginatedDataCollection|Lazy $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeTrue()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::DataPaginatedCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(PaginatedDataCollection::class)
        ->acceptedTypes->toHaveKeys([PaginatedDataCollection::class]);
});

it('can deduce a cursor paginated data collection type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public CursorPaginatedDataCollection $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeFalse()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::DataCursorPaginatedCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(CursorPaginatedDataCollection::class)
        ->acceptedTypes->toHaveKeys([CursorPaginatedDataCollection::class]);
});

it('can deduce a cursor paginated data collection union type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public CursorPaginatedDataCollection|Lazy $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeTrue()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::DataCursorPaginatedCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(CursorPaginatedDataCollection::class)
        ->acceptedTypes->toHaveKeys([CursorPaginatedDataCollection::class]);
});

it('can deduce an array data collection type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public array $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeFalse()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::Array)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe('array')
        ->acceptedTypes->toHaveKeys(['array']);
});

it('can deduce an array data collection union type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public array|Lazy $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeTrue()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::Array)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe('array')
        ->acceptedTypes->toHaveKeys(['array']);
});

it('can deduce an enumerable data collection type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public Collection $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeFalse()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::Enumerable)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(Collection::class)
        ->acceptedTypes->toHaveKeys([Collection::class]);
});

it('can deduce an enumerable data collection union type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public Collection|Lazy $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeTrue()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::Enumerable)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(Collection::class)
        ->acceptedTypes->toHaveKeys([Collection::class]);
});

it('can deduce a paginator data collection type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public LengthAwarePaginator $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeFalse()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::Paginator)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(LengthAwarePaginator::class)
        ->acceptedTypes->toHaveKeys([LengthAwarePaginator::class]);
});

it('can deduce a paginator data collection union type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public LengthAwarePaginator|Lazy $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeTrue()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::Paginator)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(LengthAwarePaginator::class)
        ->acceptedTypes->toHaveKeys([LengthAwarePaginator::class]);
});

it('can deduce a cursor paginator data collection type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public CursorPaginator $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeFalse()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::CursorPaginator)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(CursorPaginator::class)
        ->acceptedTypes->toHaveKeys([CursorPaginator::class]);
});

it('can deduce a cursor paginator data collection union type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public CursorPaginator|Lazy $property;
    });

    expect($type)
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->isLazy->toBeTrue()
        ->isOptional->toBeFalse()
        ->kind->toBe(DataTypeKind::CursorPaginator)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(CursorPaginator::class)
        ->acceptedTypes->toHaveKeys([CursorPaginator::class]);
});

it('cannot have multiple data types', function () {
    resolveDataType(new class () {
        public SimpleData|ComplicatedData $property;
    });
})->throws(InvalidDataType::class);

it('cannot combine a data object and another type', function () {
    resolveDataType(new class () {
        public SimpleData|int $property;
    });
})->throws(InvalidDataType::class);

it('cannot combine a data collection and another type', function () {
    resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection|int $property;
    });
})->throws(InvalidDataType::class);

it(
    'will resolve the base types for accepted types',
    function (object $class, array $expected) {
        expect(resolveDataType($class)->acceptedTypes)->toEqualCanonicalizing($expected);
    }
)->with(function () {
    yield 'no type' => [
        'class' => new class () {
            public $property;
        },
        'expected' => [],
    ];

    yield 'mixed' => [
        'class' => new class () {
            public mixed $property;
        },
        'expected' => [],
    ];

    yield 'single' => [
        'class' => new class () {
            public string $property;
        },
        'expected' => ['string' => []],
    ];

    yield 'multi' => [
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

    yield 'data' => [
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
                EmptyData::class,
            ],
        ],
    ];

    yield 'enum' => [
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

it(
    'can check if a data type accepts a type',
    function (object $class, string $type, bool $accepts) {
        expect(resolveDataType($class))->acceptsType($type)->toEqual($accepts);
    }
)->with(function () {
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

it(
    'can check if a data type accepts a value',
    function (object $class, mixed $value, bool $accepts) {
        expect(resolveDataType($class))->acceptsValue($value)->toEqual($accepts);
    }
)->with(function () {
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

it(
    'can find accepted type for a base type',
    function (object $class, string $type, ?string $expectedType) {
        expect(resolveDataType($class))
            ->findAcceptedTypeForBaseType($type)->toEqual($expectedType);
    }
)->with(function () {
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

it('can annotate data collections using attributes', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection $property;
    });

    expect($type)
        ->kind->toBe(DataTypeKind::DataCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(DataCollection::class);
});

it('can annotate data collections using var annotations', function () {
    $type = resolveDataType(new class () {
        /** @var DataCollection<SimpleData> */
        public DataCollection $property;
    });

    expect($type)
        ->kind->toBe(DataTypeKind::DataCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(DataCollection::class);
});

it('can annotate data collections using property annotations', function () {
    /**
     * @property DataCollection<SimpleData> $property
     */
    class TestDataTypeWithClassAnnotatedProperty{
        public function __construct(
            public DataCollection $property,
        ) {
        }
    }

    $type = resolveDataType(new \TestDataTypeWithClassAnnotatedProperty(SimpleData::collection([])));

    expect($type)
        ->kind->toBe(DataTypeKind::DataCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(DataCollection::class);
});
