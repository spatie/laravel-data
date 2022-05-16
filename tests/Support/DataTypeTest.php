<?php

namespace Spatie\LaravelData\Tests\Support;

use BackedEnum;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Generator;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;
use ReflectionProperty;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\CannotFindDataClass;
use Spatie\LaravelData\Exceptions\InvalidDataType;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Support\DataType;
use Spatie\LaravelData\Tests\Fakes\CollectionAnnotationsData;
use Spatie\LaravelData\Tests\Fakes\ComplicatedData;
use Spatie\LaravelData\Tests\Fakes\DummyBackedEnum;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedProperty;
use Spatie\LaravelData\Tests\TestCase;
use UnitEnum;

class DataTypeTest extends TestCase
{
    /** @test */
    public function it_can_deduce_a_type_without_definition()
    {
        $type = $this->resolveDataType(new class () {
            public $property;
        });

        $this->assertTrue($type->isNullable);
        $this->assertTrue($type->isMixed);
        $this->assertFalse($type->isLazy);
        $this->assertFalse($type->isUndefinable);
        $this->assertFalse($type->isDataObject);
        $this->assertFalse($type->isDataCollection);
        $this->assertNull($type->dataClass);
        $this->assertEmpty($type->acceptedTypes);
    }

    /** @test */
    public function it_can_deduce_a_type_with_definition()
    {
        $type = $this->resolveDataType(new class () {
            public string $property;
        });

        $this->assertFalse($type->isNullable);
        $this->assertFalse($type->isMixed);
        $this->assertFalse($type->isLazy);
        $this->assertFalse($type->isUndefinable);
        $this->assertFalse($type->isDataObject);
        $this->assertFalse($type->isDataCollection);
        $this->assertNull($type->dataClass);
        $this->assertEquals(['string'], array_keys($type->acceptedTypes));
    }

    /** @test */
    public function it_can_deduce_a_nullable_type_with_definition()
    {
        $type = $this->resolveDataType(new class () {
            public ?string $property;
        });

        $this->assertTrue($type->isNullable);
        $this->assertFalse($type->isMixed);
        $this->assertFalse($type->isLazy);
        $this->assertFalse($type->isUndefinable);
        $this->assertFalse($type->isDataObject);
        $this->assertFalse($type->isDataCollection);
        $this->assertNull($type->dataClass);
        $this->assertEquals(['string'], array_keys($type->acceptedTypes));
    }

    /** @test */
    public function it_can_deduce_a_union_type_definition()
    {
        $type = $this->resolveDataType(new class () {
            public string|int $property;
        });

        $this->assertFalse($type->isNullable);
        $this->assertFalse($type->isMixed);
        $this->assertFalse($type->isLazy);
        $this->assertFalse($type->isUndefinable);
        $this->assertFalse($type->isDataObject);
        $this->assertFalse($type->isDataCollection);
        $this->assertNull($type->dataClass);
        $this->assertEquals(['string', 'int'], array_keys($type->acceptedTypes));
    }

    /** @test */
    public function it_can_deduce_a_nullable_union_type_definition()
    {
        $type = $this->resolveDataType(new class () {
            public string|int|null $property;
        });

        $this->assertTrue($type->isNullable);
        $this->assertFalse($type->isMixed);
        $this->assertFalse($type->isLazy);
        $this->assertFalse($type->isUndefinable);
        $this->assertFalse($type->isDataObject);
        $this->assertFalse($type->isDataCollection);
        $this->assertNull($type->dataClass);
        $this->assertEquals(['string', 'int'], array_keys($type->acceptedTypes));
    }

    /** @test */
    public function it_can_deduce_an_intersection_type_definition()
    {
        $type = $this->resolveDataType(new class () {
            public DateTime & DateTimeImmutable $property;
        });

        $this->assertFalse($type->isNullable);
        $this->assertFalse($type->isMixed);
        $this->assertFalse($type->isLazy);
        $this->assertFalse($type->isUndefinable);
        $this->assertFalse($type->isDataObject);
        $this->assertFalse($type->isDataCollection);
        $this->assertNull($type->dataClass);
        $this->assertEquals([
            DateTime::class,
            DateTimeImmutable::class,
        ], array_keys($type->acceptedTypes));
    }

    /** @test */
    public function it_can_deduce_a_mixed_type()
    {
        $type = $this->resolveDataType(new class () {
            public mixed $property;
        });

        $this->assertTrue($type->isNullable);
        $this->assertTrue($type->isMixed);
        $this->assertFalse($type->isLazy);
        $this->assertFalse($type->isUndefinable);
        $this->assertFalse($type->isDataObject);
        $this->assertFalse($type->isDataCollection);
        $this->assertNull($type->dataClass);
        $this->assertEquals([], $type->acceptedTypes);
    }

    /** @test */
    public function it_can_deduce_a_lazy_type()
    {
        $type = $this->resolveDataType(new class () {
            public string|Lazy $property;
        });

        $this->assertFalse($type->isNullable);
        $this->assertFalse($type->isMixed);
        $this->assertTrue($type->isLazy);
        $this->assertFalse($type->isUndefinable);
        $this->assertFalse($type->isDataObject);
        $this->assertFalse($type->isDataCollection);
        $this->assertNull($type->dataClass);
        $this->assertEquals(['string'], array_keys($type->acceptedTypes));
    }

    /** @test */
    public function it_can_deduce_an_optional_type()
    {
        $type = $this->resolveDataType(new class () {
            public string|Optional $property;
        });

        $this->assertFalse($type->isNullable);
        $this->assertFalse($type->isMixed);
        $this->assertFalse($type->isLazy);
        $this->assertTrue($type->isUndefinable);
        $this->assertFalse($type->isDataObject);
        $this->assertFalse($type->isDataCollection);
        $this->assertNull($type->dataClass);
        $this->assertEquals(['string'], array_keys($type->acceptedTypes));
    }

    /** @test */
    public function a_type_cannot_be_optional_alone()
    {
        $this->expectException(InvalidDataType::class);

        $this->resolveDataType(new class () {
            public Optional $property;
        });
    }

    /** @test */
    public function it_can_deduce_a_data_type()
    {
        $type = $this->resolveDataType(new class () {
            public SimpleData $property;
        });

        $this->assertFalse($type->isNullable);
        $this->assertFalse($type->isMixed);
        $this->assertFalse($type->isLazy);
        $this->assertFalse($type->isUndefinable);
        $this->assertTrue($type->isDataObject);
        $this->assertFalse($type->isDataCollection);
        $this->assertEquals(SimpleData::class, $type->dataClass);
        $this->assertEquals([SimpleData::class], array_keys($type->acceptedTypes));
    }

    /** @test */
    public function it_can_deduce_a_data_union_type()
    {
        $type = $this->resolveDataType(new class () {
            public SimpleData|Lazy $property;
        });

        $this->assertFalse($type->isNullable);
        $this->assertFalse($type->isMixed);
        $this->assertTrue($type->isLazy);
        $this->assertFalse($type->isUndefinable);
        $this->assertTrue($type->isDataObject);
        $this->assertFalse($type->isDataCollection);
        $this->assertEquals(SimpleData::class, $type->dataClass);
        $this->assertEquals([SimpleData::class], array_keys($type->acceptedTypes));
    }

    /** @test */
    public function it_can_deduce_a_data_collection_type()
    {
        $type = $this->resolveDataType(new class () {
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection $property;
        });

        $this->assertFalse($type->isNullable);
        $this->assertFalse($type->isMixed);
        $this->assertFalse($type->isLazy);
        $this->assertFalse($type->isUndefinable);
        $this->assertFalse($type->isDataObject);
        $this->assertTrue($type->isDataCollection);
        $this->assertEquals(SimpleData::class, $type->dataClass);
        $this->assertEquals([DataCollection::class], array_keys($type->acceptedTypes));
    }

    /** @test */
    public function it_can_deduce_a_data_collection_union_type()
    {
        $type = $this->resolveDataType(new class () {
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection|Lazy $property;
        });

        $this->assertFalse($type->isNullable);
        $this->assertFalse($type->isMixed);
        $this->assertTrue($type->isLazy);
        $this->assertFalse($type->isUndefinable);
        $this->assertFalse($type->isDataObject);
        $this->assertTrue($type->isDataCollection);
        $this->assertEquals(SimpleData::class, $type->dataClass);
        $this->assertEquals([DataCollection::class], array_keys($type->acceptedTypes));
    }

    /** @test */
    public function it_cannot_have_multiple_data_types()
    {
        $this->expectException(InvalidDataType::class);

        $this->resolveDataType(new class () {
            public SimpleData|ComplicatedData $property;
        });
    }

    /** @test */
    public function it_cannot_combine_a_data_object_and_another_type()
    {
        $this->expectException(InvalidDataType::class);

        $this->resolveDataType(new class () {
            public SimpleData|int $property;
        });
    }


    /** @test */
    public function it_cannot_combine_a_data_collection_and_another_type()
    {
        $this->expectException(InvalidDataType::class);

        $this->resolveDataType(new class () {
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection|int $property;
        });
    }

    /**
     * @test
     * @dataProvider acceptedBaseTypesDataProvider
     */
    public function it_will_resolve_the_base_types_for_accepted_types(
        object $class,
        array $expected,
    ) {
        $this->assertEquals($expected, $this->resolveDataType($class)->acceptedTypes);
    }

    public function acceptedBaseTypesDataProvider(): Generator
    {
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
    }

    /**
     * @test
     * @dataProvider acceptTypeDataProvider
     */
    public function it_can_check_if_a_data_type_accepts_a_type(
        object $class,
        string $type,
        bool $accepts
    ) {
        $this->assertEquals($accepts, $this->resolveDataType($class)->acceptsType($type));
    }

    public function acceptTypeDataProvider(): Generator
    {
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
    }

    /**
     * @test
     * @dataProvider acceptValueDataProvider
     */
    public function it_can_check_if_a_data_type_accepts_a_value(
        object $class,
        mixed $value,
        bool $accepts
    ) {
        $this->assertEquals($accepts, $this->resolveDataType($class)->acceptsValue($value));
    }

    public function acceptValueDataProvider(): Generator
    {
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
    }

    /**
     * @test
     * @dataProvider acceptedTypeForBaseTypesDataProvider
     */
    public function it_can_find_an_accepted_type_for_a_base_type(
        object $class,
        string $type,
        ?string $expectedType
    ) {
        $this->assertEquals($expectedType, $this->resolveDataType($class)->findAcceptedTypeForBaseType($type));
    }

    public function acceptedTypeForBaseTypesDataProvider(): Generator
    {
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
    }

    /**
     * @test
     * @dataProvider correctAnnotationsDataProvider
     */
    public function it_can_get_the_data_class_for_a_data_collection_by_annotation(
        string $property,
        ?string $expected
    ) {
        $dataType = DataType::create(new ReflectionProperty(CollectionAnnotationsData::class, $property));

        $this->assertEquals($expected, $dataType->dataClass);
    }

    public function correctAnnotationsDataProvider(): Generator
    {
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
    }

    /**
     * @test
     * @dataProvider invalidAnnotationsDataProvider
     */
    public function it_cannot_get_the_data_class_for_invalid_annotations(
        string $property,
    ) {
        $this->expectException(CannotFindDataClass::class);

        DataType::create(new ReflectionProperty(CollectionAnnotationsData::class, $property));
    }

    public function invalidAnnotationsDataProvider(): Generator
    {
        yield [
            'property' => 'propertyM',
        ];

        yield [
            'property' => 'propertyN',
        ];

        yield [
            'property' => 'propertyO',
        ];
    }

    private function resolveDataType(object $class, string $property = 'property'): DataType
    {
        return DataType::create(new ReflectionProperty($class, $property));
    }
}
