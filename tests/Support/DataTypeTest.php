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
use Spatie\LaravelData\Contracts\ContextableData;
use Spatie\LaravelData\Contracts\DataObject;
use Spatie\LaravelData\Contracts\EmptyData;
use Spatie\LaravelData\Contracts\IncludeableData;
use Spatie\LaravelData\Contracts\ResponsableData;
use Spatie\LaravelData\Contracts\TransformableData;
use Spatie\LaravelData\Contracts\ValidateableData;
use Spatie\LaravelData\Contracts\WrappableData;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Enums\DataTypeKind;
use Spatie\LaravelData\Exceptions\InvalidDataType;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\DataType;
use Spatie\LaravelData\Support\Lazy\ClosureLazy;
use Spatie\LaravelData\Support\Lazy\ConditionalLazy;
use Spatie\LaravelData\Support\Lazy\InertiaLazy;
use Spatie\LaravelData\Support\Lazy\RelationalLazy;
use Spatie\LaravelData\Support\Types\IntersectionType;
use Spatie\LaravelData\Support\Types\NamedType;
use Spatie\LaravelData\Support\Types\UnionType;
use Spatie\LaravelData\Tests\Factories\FakeDataStructureFactory;
use Spatie\LaravelData\Tests\Fakes\ComplicatedData;
use Spatie\LaravelData\Tests\Fakes\Enums\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedProperty;

function resolveDataType(object $class, string $property = 'property'): DataType
{
    $class = FakeDataStructureFactory::class($class);

    return $class->properties->get($property)->type;
}

it('can deduce a type without definition', function () {
    $type = resolveDataType(new class () {
        public $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeTrue()
        ->isMixed->toBeTrue()
        ->lazyType->toBeNull()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull()
        ->getAcceptedTypes()->toBe([]);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe('mixed')
        ->builtIn->toBeTrue()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull();
});

it('can deduce a type with definition', function () {
    $type = resolveDataType(new class () {
        public string $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBeNull()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull()
        ->getAcceptedTypes()->toHaveKeys(['string']);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe('string')
        ->builtIn->toBeTrue()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull();
});

it('can deduce a nullable type with definition', function () {
    $type = resolveDataType(new class () {
        public ?string $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeTrue()
        ->isMixed->toBeFalse()
        ->lazyType->toBeNull()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectionClass->toBeNull()
        ->getAcceptedTypes()->toHaveKeys(['string']);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe('string')
        ->builtIn->toBeTrue()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull();
});

it('can deduce a union type definition', function () {
    $type = resolveDataType(new class () {
        public string|int $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBeNull()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull()
        ->getAcceptedTypes()->toHaveKeys(['string', 'int']);

    expect($type->type)
        ->toBeInstanceOf(UnionType::class);
});

it('can deduce a nullable union type definition', function () {
    $type = resolveDataType(new class () {
        public string|int|null $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeTrue()
        ->isMixed->toBeFalse()
        ->lazyType->toBeNull()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull()
        ->getAcceptedTypes()->toHaveKeys(['string', 'int']);

    expect($type->type)
        ->toBeInstanceOf(UnionType::class);
});

it('can deduce an intersection type definition', function () {
    $type = resolveDataType(new class () {
        public DateTime & DateTimeImmutable $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBeNull()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull()
        ->getAcceptedTypes()->toHaveKeys([
            DateTime::class,
            DateTimeImmutable::class,
        ]);

    expect($type->type)
        ->toBeInstanceOf(IntersectionType::class);
});

it('can deduce a nullable intersection type definition', function () {
    $code = '$type = resolveDataType(new class () {public (DateTime & DateTimeImmutable)|null $property;});';

    eval($code); // We support PHP 8.1 which craches on this

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeTrue()
        ->isMixed->toBeFalse()
        ->lazyType->toBeNull()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull()
        ->getAcceptedTypes()->toHaveKeys([
            DateTime::class,
            DateTimeImmutable::class,
        ]);

    expect($type->type)
        ->toBeInstanceOf(IntersectionType::class);
})->skipOnPhp('<8.2');

it('can deduce a mixed type', function () {
    $type = resolveDataType(new class () {
        public mixed $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeTrue()
        ->isMixed->toBeTrue()
        ->lazyType->toBeNull()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull()
        ->getAcceptedTypes()->toBeEmpty();

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe('mixed')
        ->builtIn->toBeTrue()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull();
});

it('can deduce a lazy type', function () {
    $type = resolveDataType(new class () {
        public string|Lazy $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBe(Lazy::class)
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull()
        ->getAcceptedTypes()->toHaveKeys(['string']);


    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe('string')
        ->builtIn->toBeTrue()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull();
});

it('can deduce an optional type', function () {
    $type = resolveDataType(new class () {
        public string|Optional $property;
    });

    expect($type)
        ->isOptional->toBeTrue()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBeNull()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull()
        ->getAcceptedTypes()->toHaveKeys(['string']);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe('string')
        ->builtIn->toBeTrue()
        ->kind->toBe(DataTypeKind::Default)
        ->dataClass->toBeNull()
        ->dataCollectableClass->toBeNull();
});

it('can deduce a data type', function () {
    $type = resolveDataType(new class () {
        public SimpleData $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBeNull()
        ->kind->toBe(DataTypeKind::DataObject)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBeNull()
        ->getAcceptedTypes()->toHaveKeys([SimpleData::class]);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe(SimpleData::class)
        ->builtIn->toBeFalse()
        ->kind->toBe(DataTypeKind::DataObject)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBeNull();
});

it('can deduce a data union type', function () {
    $type = resolveDataType(new class () {
        public SimpleData|Lazy $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBe(Lazy::class)
        ->kind->toBe(DataTypeKind::DataObject)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBeNull()
        ->getAcceptedTypes()->toHaveKeys([SimpleData::class]);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe(SimpleData::class)
        ->builtIn->toBeFalse()
        ->kind->toBe(DataTypeKind::DataObject)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBeNull();
});

it('can deduce a data collection type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBeNull()
        ->kind->toBe(DataTypeKind::DataCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(DataCollection::class)
        ->getAcceptedTypes()->toHaveKeys([DataCollection::class]);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe(DataCollection::class)
        ->builtIn->toBeFalse()
        ->kind->toBe(DataTypeKind::DataCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(DataCollection::class);
});

it('can deduce a data collection union type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection|Lazy $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBe(Lazy::class)
        ->kind->toBe(DataTypeKind::DataCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(DataCollection::class)
        ->getAcceptedTypes()->toHaveKeys([DataCollection::class]);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe(DataCollection::class)
        ->builtIn->toBeFalse()
        ->kind->toBe(DataTypeKind::DataCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(DataCollection::class);
});

it('can deduce a paginated data collection type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public PaginatedDataCollection $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBeNull()
        ->kind->toBe(DataTypeKind::DataPaginatedCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(PaginatedDataCollection::class)
        ->getAcceptedTypes()->toHaveKeys([PaginatedDataCollection::class]);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe(PaginatedDataCollection::class)
        ->builtIn->toBeFalse()
        ->kind->toBe(DataTypeKind::DataPaginatedCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(PaginatedDataCollection::class);
});

it('can deduce a paginated data collection union type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public PaginatedDataCollection|Lazy $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBe(Lazy::class)
        ->kind->toBe(DataTypeKind::DataPaginatedCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(PaginatedDataCollection::class)
        ->getAcceptedTypes()->toHaveKeys([PaginatedDataCollection::class]);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe(PaginatedDataCollection::class)
        ->builtIn->toBeFalse()
        ->kind->toBe(DataTypeKind::DataPaginatedCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(PaginatedDataCollection::class);
});

it('can deduce a cursor paginated data collection type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public CursorPaginatedDataCollection $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBeNull()
        ->kind->toBe(DataTypeKind::DataCursorPaginatedCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(CursorPaginatedDataCollection::class)
        ->getAcceptedTypes()->toHaveKeys([CursorPaginatedDataCollection::class]);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe(CursorPaginatedDataCollection::class)
        ->builtIn->toBeFalse()
        ->kind->toBe(DataTypeKind::DataCursorPaginatedCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(CursorPaginatedDataCollection::class);
});

it('can deduce a cursor paginated data collection union type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public CursorPaginatedDataCollection|Lazy $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBe(Lazy::class)
        ->kind->toBe(DataTypeKind::DataCursorPaginatedCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(CursorPaginatedDataCollection::class)
        ->getAcceptedTypes()->toHaveKeys([CursorPaginatedDataCollection::class]);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe(CursorPaginatedDataCollection::class)
        ->builtIn->toBeFalse()
        ->kind->toBe(DataTypeKind::DataCursorPaginatedCollection)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(CursorPaginatedDataCollection::class);
});

it('can deduce an array data collection type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public array $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBeNull()
        ->kind->toBe(DataTypeKind::Array)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe('array')
        ->getAcceptedTypes()->toHaveKeys(['array']);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe('array')
        ->builtIn->toBeTrue()
        ->kind->toBe(DataTypeKind::Array)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe('array');
});

it('can deduce an array data collection union type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public array|Lazy $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBe(Lazy::class)
        ->kind->toBe(DataTypeKind::Array)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe('array')
        ->getAcceptedTypes()->toHaveKeys(['array']);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe('array')
        ->builtIn->toBeTrue()
        ->kind->toBe(DataTypeKind::Array)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe('array');
});

it('can deduce an enumerable data collection type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public Collection $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBeNull()
        ->kind->toBe(DataTypeKind::Enumerable)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(Collection::class)
        ->getAcceptedTypes()->toHaveKeys([Collection::class]);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe(Collection::class)
        ->builtIn->toBeFalse()
        ->kind->toBe(DataTypeKind::Enumerable)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(Collection::class);
});

it('can deduce an enumerable data collection union type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public Collection|Lazy $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBe(Lazy::class)
        ->kind->toBe(DataTypeKind::Enumerable)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(Collection::class)
        ->getAcceptedTypes()->toHaveKeys([Collection::class]);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe(Collection::class)
        ->builtIn->toBeFalse()
        ->kind->toBe(DataTypeKind::Enumerable)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(Collection::class);
});

it('can deduce a paginator data collection type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public LengthAwarePaginator $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBeNull()
        ->kind->toBe(DataTypeKind::Paginator)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(LengthAwarePaginator::class)
        ->getAcceptedTypes()->toHaveKeys([LengthAwarePaginator::class]);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe(LengthAwarePaginator::class)
        ->builtIn->toBeFalse()
        ->kind->toBe(DataTypeKind::Paginator)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(LengthAwarePaginator::class);
});

it('can deduce a paginator data collection union type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public LengthAwarePaginator|Lazy $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBe(Lazy::class)
        ->kind->toBe(DataTypeKind::Paginator)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(LengthAwarePaginator::class)
        ->getAcceptedTypes()->toHaveKeys([LengthAwarePaginator::class]);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe(LengthAwarePaginator::class)
        ->builtIn->toBeFalse()
        ->kind->toBe(DataTypeKind::Paginator)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(LengthAwarePaginator::class);
});

it('can deduce a cursor paginator data collection type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public CursorPaginator $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBeNull()
        ->kind->toBe(DataTypeKind::CursorPaginator)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(CursorPaginator::class)
        ->getAcceptedTypes()->toHaveKeys([CursorPaginator::class]);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe(CursorPaginator::class)
        ->builtIn->toBeFalse()
        ->kind->toBe(DataTypeKind::CursorPaginator)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(CursorPaginator::class);
});

it('can deduce a cursor paginator data collection union type', function () {
    $type = resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public CursorPaginator|Lazy $property;
    });

    expect($type)
        ->isOptional->toBeFalse()
        ->isNullable->toBeFalse()
        ->isMixed->toBeFalse()
        ->lazyType->toBe(Lazy::class)
        ->kind->toBe(DataTypeKind::CursorPaginator)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(CursorPaginator::class)
        ->getAcceptedTypes()->toHaveKeys([CursorPaginator::class]);

    expect($type->type)
        ->toBeInstanceOf(NamedType::class)
        ->name->toBe(CursorPaginator::class)
        ->builtIn->toBeFalse()
        ->kind->toBe(DataTypeKind::CursorPaginator)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe(CursorPaginator::class);
});

it('cannot have multiple data types', function () {
    resolveDataType(new class () {
        public SimpleData|ComplicatedData $property;
    });
})->skip('Do we want to always check this?')->throws(InvalidDataType::class);

it('cannot combine a data object and another type', function () {
    resolveDataType(new class () {
        public SimpleData|int $property;
    });
})->skip('Do we want to always check this?')->throws(InvalidDataType::class);

it('cannot combine a data collection and another type', function () {
    resolveDataType(new class () {
        #[DataCollectionOf(SimpleData::class)]
        public DataCollection|int $property;
    });
})->skip('Do we want to always check this?')->throws(InvalidDataType::class);

it(
    'will resolve the base types for accepted types',
    function (object $class, array $expected) {
        expect(resolveDataType($class)->getAcceptedTypes())->toEqualCanonicalizing($expected);
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
                ContextableData::class,
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
            ->findAcceptedTypeForBaseType($type)
            ->toEqual($expectedType);
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
    class TestDataTypeWithClassAnnotatedProperty
    {
        public function __construct(
            public array $property,
        ) {
        }
    }

    $type = resolveDataType(new \TestDataTypeWithClassAnnotatedProperty([]));

    expect($type)
        ->kind->toBe(DataTypeKind::Array)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe('array');
});

it('can annotate data collections using constructor parameter annotations', function () {
    class TestDataTypeWithClassAnnotatedConstructorParam
    {
        /**
         * @param array<SimpleData> $property
         */
        public function __construct(
            public array $property,
        ) {
        }
    }

    $type = resolveDataType(new \TestDataTypeWithClassAnnotatedConstructorParam([]));

    expect($type)
        ->kind->toBe(DataTypeKind::Array)
        ->dataClass->toBe(SimpleData::class)
        ->dataCollectableClass->toBe('array');
});

it('can deduce the types of lazy', function () {
    $type = resolveDataType(new class () {
        public SimpleData|Lazy $property;
    });

    expect($type)->lazyType->toBe(Lazy::class);

    $type = resolveDataType(new class () {
        public SimpleData|ClosureLazy $property;
    });

    expect($type)->lazyType->toBe(ClosureLazy::class);

    $type = resolveDataType(new class () {
        public SimpleData|InertiaLazy $property;
    });

    expect($type)->lazyType->toBe(InertiaLazy::class);

    $type = resolveDataType(new class () {
        public SimpleData|ConditionalLazy $property;
    });

    expect($type)->lazyType->toBe(ConditionalLazy::class);

    $type = resolveDataType(new class () {
        public SimpleData|RelationalLazy $property;
    });

    expect($type)->lazyType->toBe(RelationalLazy::class);
});
