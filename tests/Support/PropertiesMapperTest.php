<?php

namespace Spatie\LaravelData\Tests\Support;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapFrom;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\CamelCaseToSnakeCaseMapper;
use Spatie\LaravelData\Support\PropertiesMapper;
use Spatie\LaravelData\Tests\Fakes\DataWithMapper;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\Fakes\SimpleDataWithMappedProperty;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\LaravelData\Undefined;

class PropertiesMapperTest extends TestCase
{
    private PropertiesMapper $mapper;

    public function setUp(): void
    {
        parent::setUp();

        $this->mapper = resolve(PropertiesMapper::class);
    }

    /** @test */
    public function it_can_map_property_names_using_strings()
    {
        $dataClass = new class {
            #[MapFrom('something')]
            public string $mapped;

            #[MapFrom('nested.something')]
            public string $nestedMapped;

            public string $unmapped;

            public string $undefined;
        };

        $properties = $this->mapper->execute([
            'something' => 'We are the',
            'nested' => [
                'something' => 'knights who say',
            ],
            'unmapped' => 'Ni',
        ], $dataClass::class);

        $this->assertEquals([
            'mapped' => 'We are the',
            'nestedMapped' => 'knights who say',
            'unmapped' => 'Ni',
            'undefined' => new Undefined(),
        ], $properties);
    }

    /** @test */
    public function it_can_map_property_names_using_ints()
    {
        $dataClass = new class {
            #[MapFrom(0)]
            public string $line1;

            #[MapFrom(1)]
            public string $line2;

            #[MapFrom(2)]
            public string $line3;

            public string $undefined;
        };

        $properties = $this->mapper->execute([
            'We are the knights',
            'who say',
            'Ni',
        ], $dataClass::class);

        $this->assertEquals([
            'line1' => 'We are the knights',
            'line2' => 'who say',
            'line3' => 'Ni',
            'undefined' => new Undefined(),
        ], $properties);
    }

    /** @test */
    public function it_can_map_property_names_using_a_mapper()
    {
        $dataClass = new class {
            // TODO: this is a bit confusing so we're going from snake -> camel but the class name says something different
            #[MapFrom(CamelCaseToSnakeCaseMapper::class)]
            public string $mappedCasedProperty;
        };

        $properties = $this->mapper->execute([
            'mapped_cased_property' => 'We are the knights whi say ni',
        ], $dataClass::class);

        $this->assertEquals([
            'mappedCasedProperty' => 'We are the knights whi say ni',
        ], $properties);
    }

    /** @test */
    public function it_can_map_when_the_property_is_a_data_object()
    {
        $dataClass = new class {
            #[MapFrom('something')]
            public SimpleData $mapped;

            #[MapFrom('nested.something')]
            public SimpleData $nestedMapped;

            public SimpleData $unmapped;

            public string $undefined;
        };
        $properties = $this->mapper->execute([
            'something' => ['string' => 'We are the'],
            'nested' => [
                'something' => ['string' => 'knights who say'],
            ],
            'unmapped' => ['string' => 'Ni'],
        ], $dataClass::class);

        $this->assertEquals([
            'mapped' => ['string' => 'We are the'],
            'nestedMapped' => ['string' => 'knights who say'],
            'unmapped' => ['string' => 'Ni'],
            'undefined' => new Undefined(),
        ], $properties);
    }

    /** @test */
    public function it_can_map_when_the_property_is_a_data_object_with_its_own_mappings()
    {
        $dataClass = new class {
            #[MapFrom('something')]
            public SimpleDataWithMappedProperty $mapped;

            #[MapFrom('nested.something')]
            public SimpleDataWithMappedProperty $nestedMapped;

            public SimpleDataWithMappedProperty $mappedInData;

            public string $undefined;
        };
        $properties = $this->mapper->execute([
            'something' => ['description' => 'We are the'],
            'nested' => [
                'something' => ['description' => 'knights who say'],
            ],
            'mappedInData' => ['description' => 'Ni'],
        ], $dataClass::class);

        $this->assertEquals([
            'mapped' => ['string' => 'We are the'],
            'nestedMapped' => ['string' => 'knights who say'],
            'mappedInData' => ['string' => 'Ni'],
            'undefined' => new Undefined(),
        ], $properties);
    }

    /** @test */
    public function it_can_map_when_the_property_is_a_data_collection()
    {
        $dataClass = new class {
            #[MapFrom('something'), DataCollectionOf(SimpleData::class)]
            public DataCollection $mapped;

            #[MapFrom('nested.something'), DataCollectionOf(SimpleData::class)]
            public DataCollection $nestedMapped;
        };
        $properties = $this->mapper->execute([
            'something' => [
                ['string' => 'We are the knights'],
                ['string' => 'who say'],
                ['string' => 'Ni'],
            ],
            'nested' => [
                'something' => [
                    ['string' => 'Bring us a'],
                    ['string' => 'shrubbery'],
                ],
            ],
        ], $dataClass::class);

        $this->assertEquals([
            'mapped' => [
                ['string' => 'We are the knights'],
                ['string' => 'who say'],
                ['string' => 'Ni'],
            ],
            'nestedMapped' => [
                ['string' => 'Bring us a'],
                ['string' => 'shrubbery'],
            ],
        ], $properties);
    }

    /** @test */
    public function it_can_map_when_the_property_is_a_data_collection_which_also_maps_properties()
    {
        $dataClass = new class {
            #[MapFrom('something'), DataCollectionOf(SimpleDataWithMappedProperty::class)]
            public DataCollection $mapped;

            #[MapFrom('nested.something'), DataCollectionOf(SimpleDataWithMappedProperty::class)]
            public DataCollection $nestedMapped;
        };
        $properties = $this->mapper->execute([
            'something' => [
                ['description' => 'We are the knights'],
                ['description' => 'who say'],
                ['description' => 'Ni'],
            ],
            'nested' => [
                'something' => [
                    ['description' => 'Bring us a'],
                    ['description' => 'shrubbery'],
                ],
            ],
        ], $dataClass::class);

        $this->assertEquals([
            'mapped' => [
                ['string' => 'We are the knights'],
                ['string' => 'who say'],
                ['string' => 'Ni'],
            ],
            'nestedMapped' => [
                ['string' => 'Bring us a'],
                ['string' => 'shrubbery'],
            ],
        ], $properties);
    }

    /** @test */
    public function it_can_apply_a_mapper_on_a_complete_class()
    {
        $properties = $this->mapper->execute([
            'cased_property' => 'Hello there',
            'data_cased_property' =>
                ['string' => 'We are the knights who say ni'],
            'data_collection_cased_property' => [
                ['string' => 'Bring us a'],
                ['string' => 'shrubbery'],
            ],
        ], DataWithMapper::class);

        $this->assertEquals([
            'casedProperty' => 'Hello there',
            'dataCasedProperty' =>
                ['string' => 'We are the knights who say ni'],
            'dataCollectionCasedProperty' => [
                ['string' => 'Bring us a'],
                ['string' => 'shrubbery'],
            ],
        ], $properties);
    }
}
