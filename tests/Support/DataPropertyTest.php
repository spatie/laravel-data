<?php

namespace Spatie\LaravelData\Tests\Support;

use Generator;
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
use Spatie\LaravelData\Exceptions\InvalidDataType;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Tests\Fakes\CollectionAnnotationsData;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\LaravelData\Undefined;

class DataPropertyTest extends TestCase
{
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

    private function resolveHelper(object $class, bool $hasDefaultValue = false, mixed $defaultValue = null): DataProperty
    {
        $reflectionProperty = new ReflectionProperty($class, 'property');

        return DataProperty::create($reflectionProperty, $hasDefaultValue, $defaultValue);
    }
}
