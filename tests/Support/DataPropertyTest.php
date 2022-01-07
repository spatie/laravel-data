<?php

namespace Spatie\LaravelData\Tests\Support;

use Generator;
use ReflectionProperty;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\CannotFindDataTypeForProperty;
use Spatie\LaravelData\Exceptions\InvalidDataPropertyType;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\CollectionAnnotationsData;
use Spatie\LaravelData\Tests\Fakes\FakeEnum;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;

class DataPropertyTest extends TestCase
{
    /** @test */
    public function it_works_with_non_typed_properties()
    {
        $helper = $this->resolveHelper(new class () {
            public $property;
        });

        $this->assertFalse($helper->isLazy());
        $this->assertTrue($helper->isNullable());
        $this->assertTrue($helper->isBuiltIn());
        $this->assertFalse($helper->isData());
        $this->assertFalse($helper->isDataCollection());
        $this->assertTrue($helper->types()->isEmpty());
        $this->assertEquals('property', $helper->name());
        $this->assertEquals([], $helper->validationAttributes());
    }

    /** @test */
    public function it_can_check_if_a_property_is_lazy()
    {
        $helper = $this->resolveHelper(new class () {
            public int $property;
        });

        $this->assertFalse($helper->isLazy());

        $helper = $this->resolveHelper(new class () {
            public int|Lazy $property;
        });

        $this->assertTrue($helper->isLazy());

        $helper = $this->resolveHelper(new class () {
            public int|Lazy|null $property;
        });

        $this->assertTrue($helper->isLazy());
    }

    /** @test */
    public function it_can_check_if_a_property_is_nullable()
    {
        $helper = $this->resolveHelper(new class () {
            public int $property;
        });

        $this->assertFalse($helper->isNullable());

        $helper = $this->resolveHelper(new class () {
            public ?int $property;
        });

        $this->assertTrue($helper->isNullable());

        $helper = $this->resolveHelper(new class () {
            public null|int $property;
        });

        $this->assertTrue($helper->isNullable());
    }

    /** @test */
    public function it_can_check_if_a_property_is_a_data_object()
    {
        $helper = $this->resolveHelper(new class () {
            public int $property;
        });

        $this->assertFalse($helper->isData());

        $helper = $this->resolveHelper(new class () {
            public SimpleData $property;
        });

        $this->assertTrue($helper->isData());

        $helper = $this->resolveHelper(new class () {
            public SimpleData|Lazy $property;
        });

        $this->assertTrue($helper->isData());
    }

    /** @test */
    public function it_can_check_if_a_property_is_a_data_collection()
    {
        $helper = $this->resolveHelper(new class () {
            public int $property;
        });

        $this->assertFalse($helper->isDataCollection());

        $helper = $this->resolveHelper(new class () {
            public DataCollection $property;
        });

        $this->assertTrue($helper->isDataCollection());

        $helper = $this->resolveHelper(new class () {
            public DataCollection|Lazy $property;
        });

        $this->assertTrue($helper->isDataCollection());
    }

    /** @test */
    public function it_can_check_if_a_property_is_built_in()
    {
        $helper = $this->resolveHelper(new class () {
            public int $property;
        });

        $this->assertTrue($helper->isBuiltIn());

        $helper = $this->resolveHelper(new class () {
            public int|float $property;
        });

        $this->assertTrue($helper->isBuiltIn());

        $helper = $this->resolveHelper(new class () {
            public int|Lazy $property;
        });

        $this->assertTrue($helper->isBuiltIn());

        $helper = $this->resolveHelper(new class () {
            public int|Lazy|null $property;
        });

        $this->assertTrue($helper->isBuiltIn());

        $helper = $this->resolveHelper(new class () {
            public DataCollection $property;
        });

        $this->assertFalse($helper->isBuiltIn());

        $helper = $this->resolveHelper(new class () {
            public DataCollection|null $property;
        });

        $this->assertFalse($helper->isBuiltIn());

        $helper = $this->resolveHelper(new class () {
            public mixed $property;
        });

        $this->assertTrue($helper->isBuiltIn());
    }

    /** @test */
    public function it_can_recognize_the_different_built_in_types()
    {
        $helper = $this->resolveHelper(new class () {
            public int $property;
        });

        $this->assertTrue($helper->isBuiltIn());

        $helper = $this->resolveHelper(new class () {
            public float $property;
        });

        $this->assertTrue($helper->isBuiltIn());

        $helper = $this->resolveHelper(new class () {
            public bool $property;
        });

        $this->assertTrue($helper->isBuiltIn());

        $helper = $this->resolveHelper(new class () {
            public string $property;
        });

        $this->assertTrue($helper->isBuiltIn());

        $helper = $this->resolveHelper(new class () {
            public array $property;
        });

        $this->assertTrue($helper->isBuiltIn());
    }

    /** @test */
    public function it_can_recognize_an_enum_as_built_in_type()
    {
        $this->onlyPHP81();

        $helper = $this->resolveHelper(new class () {
            public FakeEnum $property;
        });

        $this->assertTrue($helper->isBuiltIn());
    }

    /** @test */
    public function it_can_get_the_correct_types_for_the_property()
    {
        $helper = $this->resolveHelper(new class () {
            public int $property;
        });

        $this->assertEquals(['int'], $helper->types()->all());

        $helper = $this->resolveHelper(new class () {
            public int|float $property;
        });

        $this->assertEquals(['int', 'float'], $helper->types()->all());

        $helper = $this->resolveHelper(new class () {
            public int|Lazy $property;
        });

        $this->assertEquals(['int'], $helper->types()->all());

        $helper = $this->resolveHelper(new class () {
            public int|Lazy|null $property;
        });

        $this->assertEquals(['int'], $helper->types()->all());
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

        $this->assertEquals([new Max(10)], $helper->validationAttributes());
    }

    /** @test */
    public function it_can_get_the_cast_attribute()
    {
        $helper = $this->resolveHelper(new class () {
            #[WithCast(DateTimeInterfaceCast::class)]
            public SimpleData $property;
        });

        $this->assertEquals(new WithCast(DateTimeInterfaceCast::class), $helper->castAttribute());
    }

    /** @test */
    public function it_can_get_the_cast_attribute_with_arguments()
    {
        $helper = $this->resolveHelper(new class () {
            #[WithCast(DateTimeInterfaceCast::class, 'd-m-y')]
            public SimpleData $property;
        });

        $this->assertEquals(new WithCast(DateTimeInterfaceCast::class, 'd-m-y'), $helper->castAttribute());
    }

    /** @test */
    public function it_can_get_the_transformer_attribute()
    {
        $helper = $this->resolveHelper(new class () {
            #[WithTransformer(DateTimeInterfaceTransformer::class)]
            public SimpleData $property;
        });

        $this->assertEquals(new WithTransformer(DateTimeInterfaceTransformer::class), $helper->transformerAttribute());
    }

    /** @test */
    public function it_can_get_the_transformer_attribute_with_arguments()
    {
        $helper = $this->resolveHelper(new class () {
            #[WithTransformer(DateTimeInterfaceTransformer::class, 'd-m-y')]
            public SimpleData $property;
        });

        $this->assertEquals(new WithTransformer(DateTimeInterfaceTransformer::class, 'd-m-y'), $helper->transformerAttribute());
    }

    /** @test */
    public function it_can_get_the_data_class_for_a_data_object()
    {
        $helper = $this->resolveHelper(new class () {
            public SimpleData $property;
        });

        $this->assertEquals(SimpleData::class, $helper->dataClassName());
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

        $this->assertEquals($expected, $dataProperty->dataClassName());
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
        $dataProperty = DataProperty::create(new ReflectionProperty(CollectionAnnotationsData::class, $property));

        $this->expectException(CannotFindDataTypeForProperty::class);

        $dataProperty->dataClassName();
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
