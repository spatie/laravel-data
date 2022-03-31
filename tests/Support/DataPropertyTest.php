<?php

namespace Spatie\LaravelData\Tests\Support;

use Countable;
use Generator;
use Illuminate\Contracts\Support\Arrayable;
use ReflectionProperty;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithoutValidation;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\CannotFindDataClass;
use Spatie\LaravelData\Exceptions\InvalidDataPropertyType;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\DataType;
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

        $this->assertFalse($helper->type->isLazy);
        $this->assertTrue($helper->type->isNullable);
        $this->assertFalse($helper->type->isUndefinable);
        $this->assertFalse($helper->type->isDataObject);
        $this->assertFalse($helper->type->isDataCollection);
        $this->assertTrue($helper->type->isEmpty());
        $this->assertEquals('property', $helper->name);
    }

    /** @test */
    public function it_can_check_if_a_property_is_lazy()
    {
        $helper = $this->resolveHelper(new class () {
            public int $property;
        });

        $this->assertFalse($helper->type->isLazy);

        $helper = $this->resolveHelper(new class () {
            public int|Lazy $property;
        });

        $this->assertTrue($helper->type->isLazy);

        $helper = $this->resolveHelper(new class () {
            public int|Lazy|null $property;
        });

        $this->assertTrue($helper->type->isLazy);
    }

    /** @test */
    public function it_can_check_if_a_property_is_nullable()
    {
        $helper = $this->resolveHelper(new class () {
            public int $property;
        });

        $this->assertFalse($helper->type->isNullable);

        $helper = $this->resolveHelper(new class () {
            public ?int $property;
        });

        $this->assertTrue($helper->type->isNullable);

        $helper = $this->resolveHelper(new class () {
            public null|int $property;
        });

        $this->assertTrue($helper->type->isNullable);
    }

    /** @test */
    public function it_can_check_if_a_property_is_undefinable()
    {
        $helper = $this->resolveHelper(new class () {
            public int $property;
        });

        $this->assertFalse($helper->type->isUndefinable);

        $helper = $this->resolveHelper(new class () {
            public Undefined|int $property;
        });

        $this->assertTrue($helper->type->isUndefinable);
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

        $this->assertFalse($helper->type->isDataObject);

        $helper = $this->resolveHelper(new class () {
            public SimpleData $property;
        });

        $this->assertTrue($helper->type->isDataObject);

        $helper = $this->resolveHelper(new class () {
            public SimpleData|Lazy $property;
        });

        $this->assertTrue($helper->type->isDataObject);
    }

    /** @test */
    public function it_can_check_if_a_property_is_a_data_collection()
    {
        $helper = $this->resolveHelper(new class () {
            public int $property;
        });

        $this->assertFalse($helper->type->isDataCollection);

        $helper = $this->resolveHelper(new class () {
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection $property;
        });

        $this->assertTrue($helper->type->isDataCollection);

        $helper = $this->resolveHelper(new class () {
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection|Lazy $property;
        });

        $this->assertTrue($helper->type->isDataCollection);
    }

    /** @test */
    public function it_can_get_the_correct_types_for_the_property()
    {
        $helper = $this->resolveHelper(new class () {
            public int $property;
        });

        $this->assertEquals(['int'], $helper->type->acceptedTypes);

        $helper = $this->resolveHelper(new class () {
            public int|float $property;
        });

        $this->assertEquals(['int', 'float'], $helper->type->acceptedTypes);

        $helper = $this->resolveHelper(new class () {
            public int|Lazy $property;
        });

        $this->assertEquals(['int'], $helper->type->acceptedTypes);

        $helper = $this->resolveHelper(new class () {
            public int|Lazy|null $property;
        });

        $this->assertEquals(['int'], $helper->type->acceptedTypes);
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
    public function it_can_get_the_mapped_input_name()
    {
        $helper = $this->resolveHelper(new class () {
            #[MapInputName('other')]
            public SimpleData $property;
        });

        $this->assertEquals('other', $helper->inputMappedName);
    }

    /** @test */
    public function it_can_get_the_mapped_output_name()
    {
        $helper = $this->resolveHelper(new class () {
            #[MapOutputName('other')]
            public SimpleData $property;
        });

        $this->assertEquals('other', $helper->outputMappedName);
    }

    /** @test */
    public function it_can_get_all_attributes()
    {
        $helper = $this->resolveHelper(new class () {
            #[MapInputName('other')]
            #[WithTransformer(DateTimeInterfaceTransformer::class)]
            #[WithCast(DateTimeInterfaceCast::class)]
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection $property;
        });

        $this->assertCount(4, $helper->attributes);
    }

    /** @test */
    public function it_can_get_the_data_class_for_a_data_object()
    {
        $helper = $this->resolveHelper(new class () {
            public SimpleData $property;
        });

        $this->assertEquals(SimpleData::class, $helper->type->dataClass);
    }

    /** @test */
    public function it_can_get_the_default_value()
    {
        $helper = $this->resolveHelper(new class () {
            public string $property;
        });

        $this->assertFalse($helper->hasDefaultValue);

        $helper = $this->resolveHelper(new class () {
            public string $property = 'hello';
        });

        $this->assertTrue($helper->hasDefaultValue);
        $this->assertEquals('hello', $helper->defaultValue);
    }

    /** @test */
    public function it_can_check_if_the_property_is_promoted()
    {
        $helper = $this->resolveHelper(new class ('') {
            public function __construct(
                public string $property,
            ) {
            }
        });

        $this->assertTrue($helper->isPromoted);

        $helper = $this->resolveHelper(new class () {
            public string $property;
        });

        $this->assertFalse($helper->isPromoted);
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

        $this->assertEquals($expected, $dataProperty->type->dataClass);
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

        DataProperty::create(new ReflectionProperty(CollectionAnnotationsData::class, $property));
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

    private function resolveHelper(object $class, bool $hasDefaultValue = false, mixed $defaultValue = null): DataProperty
    {
        $reflectionProperty = new ReflectionProperty($class, 'property');

        return DataProperty::create($reflectionProperty, $hasDefaultValue, $defaultValue);
    }
}
