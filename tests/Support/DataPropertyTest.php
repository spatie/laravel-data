<?php

namespace Spatie\LaravelData\Tests\Support;

use Countable;
use Generator;
use Illuminate\Contracts\Support\Arrayable;
use ReflectionProperty;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\CannotFindDataTypeForProperty;
use Spatie\LaravelData\Exceptions\InvalidDataPropertyType;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\DataPropertyTypes;
use Spatie\LaravelData\Tests\Fakes\CollectionAnnotationsData;
use Spatie\LaravelData\Tests\Fakes\IntersectionTypeData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\LaravelData\Undefined;

class DataPropertyTest extends TestCase
{
    /** @test */
    public function it_works_with_non_typed_properties()
    {
        $helper = $this->resolveHelper(new class () {
            public $property;
        });

        $this->assertFalse($helper->lazy);
        $this->assertTrue($helper->nullable);
        $this->assertFalse($helper->undefinable);
        $this->assertFalse($helper->isDataObject);
        $this->assertFalse($helper->isDataCollection);
        $this->assertTrue($helper->types->isEmpty());
        $this->assertEquals('property', $helper->name);
        $this->assertEquals([], $helper->validationAttributes);
    }

    /** @test */
    public function it_can_check_if_a_property_is_lazy()
    {
        $helper = $this->resolveHelper(new class () {
            public int $property;
        });

        $this->assertFalse($helper->lazy);

        $helper = $this->resolveHelper(new class () {
            public int|Lazy $property;
        });

        $this->assertTrue($helper->lazy);

        $helper = $this->resolveHelper(new class () {
            public int|Lazy|null $property;
        });

        $this->assertTrue($helper->lazy);
    }

    /** @test */
    public function it_can_check_if_a_property_is_nullable()
    {
        $helper = $this->resolveHelper(new class () {
            public int $property;
        });

        $this->assertFalse($helper->nullable);

        $helper = $this->resolveHelper(new class () {
            public ?int $property;
        });

        $this->assertTrue($helper->nullable);

        $helper = $this->resolveHelper(new class () {
            public null|int $property;
        });

        $this->assertTrue($helper->nullable);
    }

    /** @test */
    public function it_can_check_if_a_property_is_undefinable()
    {
        $helper = $this->resolveHelper(new class () {
            public int $property;
        });

        $this->assertFalse($helper->undefinable);

        $helper = $this->resolveHelper(new class () {
            public Undefined|int $property;
        });

        $this->assertTrue($helper->undefinable);
    }

    /** @test */
    public function a_property_cannot_be_undefinable_alone()
    {
        $this->expectException(InvalidDataPropertyType::class);

        $this->resolveHelper(new class () {
            public Undefined $property;
        });
    }

    /** @test */
    public function it_can_check_if_a_property_is_a_data_object()
    {
        $helper = $this->resolveHelper(new class () {
            public int $property;
        });

        $this->assertFalse($helper->isDataObject);

        $helper = $this->resolveHelper(new class () {
            public SimpleData $property;
        });

        $this->assertTrue($helper->isDataObject);

        $helper = $this->resolveHelper(new class () {
            public SimpleData|Lazy $property;
        });

        $this->assertTrue($helper->isDataObject);
    }

    /** @test */
    public function it_can_check_if_a_property_is_a_data_collection()
    {
        $helper = $this->resolveHelper(new class () {
            public int $property;
        });

        $this->assertFalse($helper->isDataCollection);

        $helper = $this->resolveHelper(new class () {
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection $property;
        });

        $this->assertTrue($helper->isDataCollection);

        $helper = $this->resolveHelper(new class () {
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection|Lazy $property;
        });

        $this->assertTrue($helper->isDataCollection);
    }

    /** @test */
    public function it_can_get_the_correct_types_for_the_property()
    {
        $helper = $this->resolveHelper(new class () {
            public int $property;
        });

        $this->assertEquals(['int'], $helper->types->all());

        $helper = $this->resolveHelper(new class () {
            public int|float $property;
        });

        $this->assertEquals(['int', 'float'], $helper->types->all());

        $helper = $this->resolveHelper(new class () {
            public int|Lazy $property;
        });

        $this->assertEquals(['int'], $helper->types->all());

        $helper = $this->resolveHelper(new class () {
            public int|Lazy|null $property;
        });

        $this->assertEquals(['int'], $helper->types->all());
    }

    /** @test */
    public function it_cannot_combine_a_data_object_and_another_type()
    {
        $this->expectException(InvalidDataPropertyType::class);

        $this->resolveHelper(new class () {
            public SimpleData|int $property;
        });
    }

    /** @test */
    public function it_cannot_combine_a_data_collection_and_another_type()
    {
        $this->expectException(InvalidDataPropertyType::class);

        $this->resolveHelper(new class () {
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection|int $property;
        });
    }

    /** @test */
    public function it_can_get_validation_attributes()
    {
        $helper = $this->resolveHelper(new class () {
            #[Max(10)]
            public SimpleData $property;
        });

        $this->assertEquals([new Max(10)], $helper->validationAttributes);
    }

    /** @test */
    public function it_can_get_the_cast_attribute()
    {
        $helper = $this->resolveHelper(new class () {
            #[WithCast(DateTimeInterfaceCast::class)]
            public SimpleData $property;
        });

        $this->assertEquals(new DateTimeInterfaceCast(), $helper->cast);
    }

    /** @test */
    public function it_can_get_the_cast_attribute_with_arguments()
    {
        $helper = $this->resolveHelper(new class () {
            #[WithCast(DateTimeInterfaceCast::class, 'd-m-y')]
            public SimpleData $property;
        });

        $this->assertEquals(new DateTimeInterfaceCast('d-m-y'), $helper->cast);
    }

    /** @test */
    public function it_can_get_the_transformer_attribute()
    {
        $helper = $this->resolveHelper(new class () {
            #[WithTransformer(DateTimeInterfaceTransformer::class)]
            public SimpleData $property;
        });

        $this->assertEquals(new DateTimeInterfaceTransformer(), $helper->transformer);
    }

    /** @test */
    public function it_can_get_the_transformer_attribute_with_arguments()
    {
        $helper = $this->resolveHelper(new class () {
            #[WithTransformer(DateTimeInterfaceTransformer::class, 'd-m-y')]
            public SimpleData $property;
        });

        $this->assertEquals(new DateTimeInterfaceTransformer('d-m-y'), $helper->transformer);
    }

    /** @test */
    public function it_can_get_the_data_class_for_a_data_object()
    {
        $helper = $this->resolveHelper(new class () {
            public SimpleData $property;
        });

        $this->assertEquals(SimpleData::class, $helper->dataClass);
    }

    /** @test */
    public function it_has_support_for_intersection_types()
    {
        $this->onlyPHP81();

        $dataProperty = DataProperty::create(new ReflectionProperty(IntersectionTypeData::class, 'intersection'));

        $this->assertEquals(new DataPropertyTypes([Arrayable::class, Countable::class]), $dataProperty->types);
    }

    /** @test */
    public function it_can_check_if_a_property_should_be_validated()
    {
        $this->assertTrue($this->resolveHelper(new class () {
            public string $property;
        })->validate);

        $this->assertFalse($this->resolveHelper(new class () {
            #[WithoutValidation]
            public string $property;
        })->validate);
    }

    /**
     * @test
     * @dataProvider correctAnnotationsDataProvider
     */
    public function it_can_get_the_data_class_for_a_data_collection_by_annotation(
        string $property,
        ?string $expected
    ) {
        $dataProperty = DataProperty::create(new ReflectionProperty(CollectionAnnotationsData::class, $property));

        $this->assertEquals($expected, $dataProperty->dataClass);
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
        $this->expectException(CannotFindDataTypeForProperty::class);

        $dataProperty = DataProperty::create(new ReflectionProperty(CollectionAnnotationsData::class, $property));
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

    private function resolveHelper(object $class): DataProperty
    {
        $reflectionProperty = new ReflectionProperty($class, 'property');

        return DataProperty::create($reflectionProperty);
    }
}
