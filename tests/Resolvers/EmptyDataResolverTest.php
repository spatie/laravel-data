<?php

namespace Spatie\LaravelData\Tests\Resolvers;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Exceptions\DataPropertyCanOnlyHaveOneType;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\Resolvers\EmptyDataResolver;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;

class EmptyDataResolverTest extends TestCase
{
    /** @test */
    public function it_will_return_null_if_the_property_has_no_type()
    {
        $this->assertEmptyPropertyValue(null, new class () {
            public $property;
        });
    }

    /** @test */
    public function it_will_return_null_if_the_property_has_a_basic_type()
    {
        $this->assertEmptyPropertyValue(null, new class () {
            public int $property;
        });

        $this->assertEmptyPropertyValue(null, new class () {
            public bool $property;
        });

        $this->assertEmptyPropertyValue(null, new class () {
            public float $property;
        });

        $this->assertEmptyPropertyValue(null, new class () {
            public string $property;
        });

        $this->assertEmptyPropertyValue(null, new class () {
            public mixed $property;
        });
    }

    /** @test */
    public function it_will_return_an_array_for_collection_types()
    {
        $this->assertEmptyPropertyValue([], new class () {
            public array $property;
        });

        $this->assertEmptyPropertyValue([], new class () {
            public Collection $property;
        });

        $this->assertEmptyPropertyValue([], new class () {
            public EloquentCollection $property;
        });

        $this->assertEmptyPropertyValue([], new class () {
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection $property;
        });
    }

    /** @test */
    public function it_will_further_transform_resources()
    {
        $this->assertEmptyPropertyValue(['string' => null], new class () {
            public SimpleData $property;
        });
    }

    /** @test */
    public function it_will_return_the_base_type_for_lazy_types()
    {
//        $this->assertEmptyPropertyValue(null, new class() {
//            public Lazy | string $property;
//        });

        $this->assertEmptyPropertyValue([], new class () {
            public Lazy|array $property;
        });

        $this->assertEmptyPropertyValue(['string' => null], new class () {
            public Lazy|SimpleData $property;
        });
    }

    /** @test */
    public function it_will_return_the_base_type_for_lazy_types_that_can_be_null()
    {
        $this->assertEmptyPropertyValue(null, new class () {
            public Lazy|string|null $property;
        });

        $this->assertEmptyPropertyValue([], new class () {
            public Lazy|array|null $property;
        });

        $this->assertEmptyPropertyValue(['string' => null], new class () {
            public Lazy|SimpleData|null $property;
        });
    }

    public function it_will_return_the_base_type_for_lazy_types_that_can_be_optional()
    {
        $this->assertEmptyPropertyValue(null, new class () {
            public Lazy|string|Optional $property;
        });

        $this->assertEmptyPropertyValue([], new class () {
            public Lazy|array|Optional $property;
        });

        $this->assertEmptyPropertyValue(['string' => null], new class () {
            public Lazy|SimpleData|Optional $property;
        });
    }

    /** @test */
    public function it_will_return_the_base_type_for_undefinable_types()
    {
        $this->assertEmptyPropertyValue(null, new class () {
            public Optional|string $property;
        });

        $this->assertEmptyPropertyValue([], new class () {
            public Optional|array $property;
        });

        $this->assertEmptyPropertyValue(['string' => null], new class () {
            public Optional|SimpleData $property;
        });
    }

    /** @test */
    public function it_cannot_have_multiple_types()
    {
        $this->expectException(DataPropertyCanOnlyHaveOneType::class);

        $this->assertEmptyPropertyValue(null, new class () {
            public int|string $property;
        });
    }

    /** @test */
    public function it_cannot_have_multiple_types_with_a_lazy()
    {
        $this->expectException(DataPropertyCanOnlyHaveOneType::class);

        $this->assertEmptyPropertyValue(null, new class () {
            public int|string|Lazy $property;
        });
    }

    /** @test */
    public function it_cannot_have_multiple_types_with_a_nullable_lazy()
    {
        $this->expectException(DataPropertyCanOnlyHaveOneType::class);

        $this->assertEmptyPropertyValue(null, new class () {
            public int|string|Lazy|null $property;
        });
    }

    /** @test */
    public function it_cannot_have_multiple_types_with_a_optional()
    {
        $this->expectException(DataPropertyCanOnlyHaveOneType::class);

        $this->assertEmptyPropertyValue(null, new class () {
            public int|string|Optional $property;
        });
    }

    /** @test */
    public function it_cannot_have_multiple_types_with_a_nullable_optional()
    {
        $this->expectException(DataPropertyCanOnlyHaveOneType::class);

        $this->assertEmptyPropertyValue(null, new class () {
            public int|string|Optional|null $property;
        });
    }

    /** @test */
    public function it_can_overwrite_empty_properties()
    {
        $this->assertEmptyPropertyValue('Hello', new class () {
            public string $property;
        }, ['property' => 'Hello']);
    }

    /** @test */
    public function it_can_use_the_property_default_value()
    {
        $this->assertEmptyPropertyValue('Hello', new class () {
            public string $property = 'Hello';
        });
    }

    /** @test */
    public function it_can_use_the_constructor_property_default_value()
    {
        $this->assertEmptyPropertyValue('Hello', new class () {
            public function __construct(
                public string $property = 'Hello',
            ) {
            }
        });
    }

    /** @test */
    public function it_has_support_for_mapping_property_names()
    {
        $this->assertEmptyPropertyValue(null, new class () {
            #[MapOutputName('other_property')]
            public string $property;
        }, propertyName: 'other_property');
    }

    private function assertEmptyPropertyValue(
        mixed $expected,
        object $class,
        array $extra = [],
        string $propertyName = 'property',
    ) {
        $resolver = app(EmptyDataResolver::class);

        $empty = $resolver->execute($class::class, $extra);

        $this->assertArrayHasKey($propertyName, $empty);
        $this->assertEquals($expected, $empty[$propertyName]);
    }
}
